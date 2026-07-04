<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\EvaluationPeriod;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Services\TeachingEvaluationReportService;
use App\Services\TimetableSchedulingService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgrammeAcademicController extends Controller
{
    public function __construct(
        protected TimetableSchedulingService $timetableScheduler,
        protected TeachingEvaluationReportService $evaluationReportService,
    ) {}

    protected function backToTab(Programme $programme, string $tab, string $message): RedirectResponse
    {
        return redirect()
            ->route('programmes.academic.index', ['programme' => $programme, 'tab' => $tab])
            ->with('success', $message);
    }

    protected function tabFromRequest(Request $request, string $default): string
    {
        $tab = $request->input('tab', $default);
        $allowed = ['courses', 'lecturers', 'offerings', 'students', 'venues', 'timetable', 'evaluations'];

        return in_array($tab, $allowed, true) ? $tab : $default;
    }

    protected function academicTabUrl(Programme $programme, string $tab): string
    {
        return route('programmes.academic.index', ['programme' => $programme, 'tab' => $tab]);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function validateForTab(Request $request, Programme $programme, string $tab, array $rules): array
    {
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray())
                ->redirectTo($this->academicTabUrl($programme, $tab));
        }

        return $validator->validated();
    }

    protected function institutionUserEmailRule(int $institutionId, ?int $ignoreUserId = null): \Illuminate\Validation\Rules\Unique
    {
        $rule = Rule::unique('users', 'email')->where('institution_id', $institutionId);

        if ($ignoreUserId !== null) {
            $rule->ignore($ignoreUserId);
        }

        return $rule;
    }

    protected function validationErrorForTab(Programme $programme, string $tab, string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message])
            ->redirectTo($this->academicTabUrl($programme, $tab));
    }

    public function index(Programme $programme): View
    {
        $this->authorize('update', $programme);

        $programme->load([
            'orgUnit',
            'courses.offerings.lecturer',
            'courses.offerings.timetableSlots.classroom',
            'courses.offerings.studentEnrolments.student',
            'students.user',
            'staffMembers',
        ]);

        $lecturers = StaffMember::query()
            ->where('programme_id', $programme->id)
            ->where('type', 'academic')
            ->with('user')
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::query()
            ->where('institution_id', $programme->institution_id)
            ->orderBy('name')
            ->get();

        $evaluationPeriods = EvaluationPeriod::query()
            ->where('institution_id', $programme->institution_id)
            ->withCount([
                'teachingEvaluations as submitted_count' => fn ($q) => $q->where('status', 'submitted'),
            ])
            ->latest('opens_at')
            ->get();

        $currentYear = (string) date('Y').'/'.((int) date('Y') + 1);

        return view('programmes.academic.index', compact(
            'programme',
            'lecturers',
            'classrooms',
            'evaluationPeriods',
            'currentYear',
        ));
    }

    public function storeCourse(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $request->validate([
            'code' => 'required|string|max:30',
            'title' => 'required|string|max:255',
            'credit_hours' => 'required|numeric|min:0.5|max:30',
            'year_level' => 'nullable|integer|min:1|max:8',
            'semester_number' => 'nullable|integer|min:1|max:3',
        ]);

        $programme->courses()->create([
            ...$validated,
            'institution_id' => $programme->institution_id,
        ]);

        return $this->backToTab($programme, $this->tabFromRequest($request, 'courses'), 'Course added to programme.');
    }

    public function updateCourse(Request $request, Programme $programme, Course $course): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($course->programme_id === $programme->id, 404);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:courses,code,'.$course->id.',id,programme_id,'.$programme->id,
            'title' => 'required|string|max:255',
            'credit_hours' => 'required|numeric|min:0.5|max:30',
            'year_level' => 'nullable|integer|min:1|max:8',
            'semester_number' => 'nullable|integer|min:1|max:3',
        ]);

        $course->update($validated);

        return $this->backToTab($programme, 'courses', 'Course updated.');
    }

    public function destroyCourse(Request $request, Programme $programme, Course $course): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($course->programme_id === $programme->id, 404);

        $course->delete();

        return $this->backToTab($programme, 'courses', 'Course removed.');
    }

    public function storeOffering(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'staff_member_id' => 'nullable|exists:staff_members,id',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|integer|min:1|max:3',
            'delivery_mode' => 'required|string|max:30',
            'enrol_all_students' => 'nullable|boolean',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        abort_unless($course->programme_id === $programme->id, 404);

        $offering = CourseOffering::create([
            'institution_id' => $programme->institution_id,
            'course_id' => $course->id,
            'staff_member_id' => $validated['staff_member_id'] ?? null,
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester'],
            'delivery_mode' => $validated['delivery_mode'],
        ]);

        if ($request->boolean('enrol_all_students')) {
            foreach ($programme->students()->where('status', 'active')->get() as $student) {
                StudentCourseEnrolment::firstOrCreate([
                    'student_id' => $student->id,
                    'course_offering_id' => $offering->id,
                ]);
            }
        }

        return $this->backToTab($programme, $this->tabFromRequest($request, 'offerings'), 'Course offering created and lecturer assigned.');
    }

    public function updateOffering(Request $request, Programme $programme, CourseOffering $offering): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($offering->course?->programme_id === $programme->id, 404);

        $validated = $request->validate([
            'staff_member_id' => 'nullable|exists:staff_members,id',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|integer|min:1|max:3',
            'delivery_mode' => 'required|string|max:30',
        ]);

        $offering->update($validated);

        return $this->backToTab($programme, 'offerings', 'Course offering updated.');
    }

    public function destroyOffering(Request $request, Programme $programme, CourseOffering $offering): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($offering->course?->programme_id === $programme->id, 404);

        $offering->delete();

        return $this->backToTab($programme, 'offerings', 'Course offering removed.');
    }

    public function storeStudent(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $this->validateForTab($request, $programme, 'students', [
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->where('institution_id', $programme->institution_id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                $this->institutionUserEmailRule($programme->institution_id),
                Rule::unique('students', 'email')->where('institution_id', $programme->institution_id),
            ],
            'year_of_study' => 'required|integer|min:1|max:8',
            'password' => 'nullable|string|min:8',
        ]);

        $password = $validated['password'] ?? 'password';

        try {
            DB::transaction(function () use ($programme, $validated, $password): void {
                $user = User::create([
                    'institution_id' => $programme->institution_id,
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $validated['email'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]);
                $user->assignRole('student');

                Student::create([
                    'institution_id' => $programme->institution_id,
                    'user_id' => $user->id,
                    'programme_id' => $programme->id,
                    'student_number' => $validated['student_number'],
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'year_of_study' => $validated['year_of_study'],
                    'status' => 'active',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            $this->validationErrorForTab(
                $programme,
                'students',
                'email',
                'This email or student number is already registered at your institution.'
            );
        }

        return $this->backToTab($programme, $this->tabFromRequest($request, 'students'), 'Student registered with portal login access.');
    }

    public function updateStudent(Request $request, Programme $programme, Student $student): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($student->programme_id === $programme->id, 404);

        $validated = $this->validateForTab($request, $programme, 'students', [
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')
                    ->where('institution_id', $programme->institution_id)
                    ->ignore($student->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')
                    ->where('institution_id', $programme->institution_id)
                    ->ignore($student->id),
                $this->institutionUserEmailRule($programme->institution_id, $student->user_id),
            ],
            'year_of_study' => 'required|integer|min:1|max:8',
        ]);

        $student->update($validated);

        if ($student->user) {
            $student->user->update([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
            ]);
        }

        return $this->backToTab($programme, 'students', 'Student updated.');
    }

    public function destroyStudent(Request $request, Programme $programme, Student $student): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($student->programme_id === $programme->id, 404);

        if ($student->user) {
            $student->user->delete();
        }

        $student->delete();

        return $this->backToTab($programme, 'students', 'Student removed.');
    }

    public function storeTimetableSlot(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $this->validateForTab($request, $programme, 'timetable', [
            'course_offering_id' => 'required|exists:course_offerings,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_type' => 'required|string|max:30',
            'venue_name' => 'nullable|string|max:255',
        ]);

        $offering = CourseOffering::with('course')->findOrFail($validated['course_offering_id']);
        abort_unless($offering->course?->programme_id === $programme->id, 404);

        $conflict = $this->timetableScheduler->validateSlot(
            $programme,
            (int) $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['classroom_id'] ?? null,
            $validated['venue_name'] ?? null,
            (int) $validated['course_offering_id'],
        );

        if ($conflict) {
            $this->validationErrorForTab($programme, 'timetable', 'start_time', $conflict);
        }

        TimetableSlot::create($validated);

        return $this->backToTab($programme, $this->tabFromRequest($request, 'timetable'), 'Timetable slot added.');
    }

    public function updateTimetableSlot(Request $request, Programme $programme, TimetableSlot $slot): RedirectResponse
    {
        $this->authorize('update', $programme);
        $slot->load('courseOffering.course');
        abort_unless($slot->courseOffering?->course?->programme_id === $programme->id, 404);

        $validated = $this->validateForTab($request, $programme, 'timetable', [
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_type' => 'required|string|max:30',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'venue_name' => 'nullable|string|max:255',
        ]);

        $conflict = $this->timetableScheduler->validateSlot(
            $programme,
            (int) $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['classroom_id'] ?? null,
            $validated['venue_name'] ?? null,
            (int) $slot->course_offering_id,
            $slot->id,
        );

        if ($conflict) {
            $this->validationErrorForTab($programme, 'timetable', 'start_time', $conflict);
        }

        $slot->update($validated);

        return $this->backToTab($programme, 'timetable', 'Timetable slot updated.');
    }

    public function autoGenerateTimetable(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $this->validateForTab($request, $programme, 'timetable', [
            'day_start' => 'required|date_format:H:i',
            'day_end' => 'required|date_format:H:i|after:day_start',
            'replace_existing' => 'nullable|boolean',
        ]);

        $classrooms = Classroom::query()
            ->where('institution_id', $programme->institution_id)
            ->orderBy('name')
            ->get();

        $rotationPass = (int) $programme->timetable_generation_pass;

        $result = $this->timetableScheduler->autoGenerate(
            $programme,
            $classrooms,
            $validated['day_start'],
            $validated['day_end'],
            $request->boolean('replace_existing', true),
            TimetableSchedulingService::DEFAULT_SESSION_MINUTES,
            $rotationPass,
        );

        if ($result['created'] > 0) {
            $programme->update(['timetable_generation_pass' => $rotationPass + 1]);
        }

        if ($result['created'] === 0 && $result['unscheduled'] === []) {
            return redirect()
                ->route('programmes.academic.index', ['programme' => $programme, 'tab' => 'timetable'])
                ->with('error', $result['message']);
        }

        return $this->backToTab($programme, 'timetable', $result['message']);
    }

    public function destroyTimetableSlot(Request $request, Programme $programme, TimetableSlot $slot): RedirectResponse
    {
        $this->authorize('update', $programme);
        $slot->load('courseOffering.course');
        abort_unless($slot->courseOffering?->course?->programme_id === $programme->id, 404);

        $slot->delete();

        return $this->backToTab($programme, 'timetable', 'Timetable slot removed.');
    }

    public function storeEvaluationPeriod(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $request->validate([
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|integer|min:1|max:3',
            'title' => 'required|string|max:255',
            'opens_at' => 'required|date',
            'closes_at' => 'required|date|after:opens_at',
        ]);

        EvaluationPeriod::create([
            'institution_id' => $programme->institution_id,
            ...$validated,
            'is_active' => true,
        ]);

        return $this->backToTab($programme, $this->tabFromRequest($request, 'evaluations'), 'Evaluation period opened for students.');
    }

    public function showEvaluationPeriod(Programme $programme, EvaluationPeriod $period): View
    {
        $this->authorize('update', $programme);
        abort_unless($period->institution_id === $programme->institution_id, 404);

        $report = $this->evaluationReportService->buildPeriodReport($programme, $period);

        return view('programmes.academic.evaluations.show', $report);
    }

    public function downloadEvaluationReport(Programme $programme, EvaluationPeriod $period): Response
    {
        $this->authorize('update', $programme);
        abort_unless($period->institution_id === $programme->institution_id, 404);

        return $this->evaluationReportService->downloadPdf($programme, $period);
    }

    public function storeClassroom(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:1000',
            'room_type' => 'required|string|max:30',
        ]);

        Classroom::create([
            'institution_id' => $programme->institution_id,
            ...$validated,
        ]);

        return $this->backToTab($programme, $this->tabFromRequest($request, 'venues'), 'Classroom added.');
    }

    public function updateClassroom(Request $request, Programme $programme, Classroom $classroom): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($classroom->institution_id === $programme->institution_id, 404);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:classrooms,code,'.$classroom->id.',id,institution_id,'.$programme->institution_id,
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:1000',
            'room_type' => 'required|string|max:30',
        ]);

        $classroom->update($validated);

        return $this->backToTab($programme, 'venues', 'Classroom updated.');
    }

    public function storeLecturer(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $createPortal = $request->boolean('create_portal_login');
        $existingUser = $createPortal
            ? User::query()
                ->where('institution_id', $programme->institution_id)
                ->where('email', $request->input('email'))
                ->first()
            : null;

        $emailRules = ['required', 'email', 'max:255'];
        if ($createPortal && ! $existingUser) {
            $emailRules[] = $this->institutionUserEmailRule($programme->institution_id);
        }

        $validated = $this->validateForTab($request, $programme, 'lecturers', [
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'designation' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'create_portal_login' => 'nullable|boolean',
        ]);

        if ($createPortal && $existingUser) {
            if ($existingUser->isStudent()) {
                $this->validationErrorForTab(
                    $programme,
                    'lecturers',
                    'email',
                    'This email is already registered as a student. Use a different email for the lecturer portal account.'
                );
            }

            $alreadyAssigned = StaffMember::query()
                ->where('programme_id', $programme->id)
                ->where('type', 'academic')
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($alreadyAssigned) {
                $this->validationErrorForTab(
                    $programme,
                    'lecturers',
                    'email',
                    'This lecturer is already assigned to this programme.'
                );
            }
        }

        $message = 'Lecturer added to programme.';

        try {
            DB::transaction(function () use ($programme, $validated, $createPortal, $existingUser, &$message): void {
                $staff = StaffMember::query()
                    ->where('programme_id', $programme->id)
                    ->where('type', 'academic')
                    ->whereNull('user_id')
                    ->where('name', $validated['name'])
                    ->first();

                if ($staff) {
                    $staff->update([
                        'designation' => $validated['designation'] ?? $staff->designation ?? 'Lecturer',
                        'qualification' => $validated['qualification'] ?? null,
                    ]);
                    $message = 'Existing lecturer record updated and linked to portal account.';
                } else {
                    $staff = StaffMember::create([
                        'institution_id' => $programme->institution_id,
                        'programme_id' => $programme->id,
                        'type' => 'academic',
                        'name' => $validated['name'],
                        'designation' => $validated['designation'] ?? 'Lecturer',
                        'qualification' => $validated['qualification'] ?? null,
                    ]);
                }

                if (! $createPortal) {
                    return;
                }

                if ($existingUser) {
                    if (! $existingUser->isLecturer()) {
                        $existingUser->assignRole('lecturer');
                    }

                    $existingUser->update(['name' => $validated['name']]);
                    $staff->update(['user_id' => $existingUser->id]);

                    return;
                }

                $user = User::create([
                    'institution_id' => $programme->institution_id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]);
                $user->assignRole('lecturer');
                $staff->update(['user_id' => $user->id]);
            });
        } catch (UniqueConstraintViolationException) {
            $this->validationErrorForTab(
                $programme,
                'lecturers',
                'email',
                'This email is already registered at your institution. Check the lecturers list — they may already exist without portal access.'
            );
        }

        return $this->backToTab($programme, $this->tabFromRequest($request, 'lecturers'), $message);
    }

    public function updateLecturer(Request $request, Programme $programme, StaffMember $staffMember): RedirectResponse
    {
        $this->authorize('update', $programme);
        abort_unless($staffMember->programme_id === $programme->id && $staffMember->type === 'academic', 404);

        $validated = $this->validateForTab($request, $programme, 'lecturers', [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                $this->institutionUserEmailRule($programme->institution_id, $staffMember->user_id),
            ],
            'designation' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
        ]);

        $staffMember->update([
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? $staffMember->designation,
            'qualification' => $validated['qualification'] ?? null,
        ]);

        if ($staffMember->user && filled($validated['email'] ?? null)) {
            $staffMember->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        return $this->backToTab($programme, 'lecturers', 'Lecturer updated.');
    }
}
