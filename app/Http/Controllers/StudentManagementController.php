<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentCourseRegistrationService;
use App\Services\StudentManagementService;
use App\Support\InstitutionScope;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;

class StudentManagementController extends Controller
{
    public function __construct(
        protected StudentManagementService $studentService,
        protected StudentCourseRegistrationService $registrationService,
    ) {}

    protected function institution(): Institution
    {
        $institutionId = InstitutionScope::institutionId();
        abort_unless($institutionId, 403, 'Select an institution to manage students.');

        return Institution::findOrFail($institutionId);
    }

    protected function institutionUserEmailRule(int $institutionId, ?int $ignoreUserId = null): Unique
    {
        $rule = Rule::unique('users', 'email')->where('institution_id', $institutionId);

        if ($ignoreUserId !== null) {
            $rule->ignore($ignoreUserId);
        }

        return $rule;
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $institution = $this->institution();

        $students = Student::query()
            ->where('institution_id', $institution->id)
            ->with('programme')
            ->when($request->query('programme_id'), fn ($q, $programmeId) => $q->where('programme_id', $programmeId))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString();

        $programmes = Programme::query()
            ->where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        return view('students.index', compact('students', 'programmes', 'institution'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Student::class);

        $institution = $this->institution();
        $programmes = Programme::query()
            ->where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        $selectedProgrammeId = $request->query('programme_id');
        $defaultEmailExample = $this->studentService->exampleEmail($institution);

        return view('students.create', compact('institution', 'programmes', 'selectedProgrammeId', 'defaultEmailExample'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $institution = $this->institution();

        $validated = $request->validate([
            'programme_id' => [
                'required',
                Rule::exists('programmes', 'id')->where('institution_id', $institution->id),
            ],
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->where('institution_id', $institution->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('students', 'email')->where('institution_id', $institution->id),
                $this->institutionUserEmailRule($institution->id),
            ],
            'year_of_study' => 'required|integer|min:1|max:8',
            'status' => 'nullable|in:active,inactive,graduated,suspended',
            'password' => 'nullable|string|min:8',
        ]);

        $studentNumber = $this->studentService->resolveStudentNumber($institution, $validated['student_number'] ?? null);
        $email = $this->studentService->resolveEmail(
            $institution,
            $validated['email'] ?? null,
            $validated['first_name'],
            $validated['last_name']
        );
        $password = $validated['password'] ?? StudentManagementService::DEFAULT_PASSWORD;

        try {
            DB::transaction(function () use ($institution, $validated, $studentNumber, $email, $password): void {
                $user = User::create([
                    'institution_id' => $institution->id,
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $email,
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]);
                $user->assignRole('student');

                Student::create([
                    'institution_id' => $institution->id,
                    'user_id' => $user->id,
                    'programme_id' => $validated['programme_id'],
                    'student_number' => $studentNumber,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'] ?? null,
                    'email' => $email,
                    'year_of_study' => $validated['year_of_study'],
                    'status' => $validated['status'] ?? 'active',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors([
                'email' => 'This email or student number is already registered at your institution.',
            ]);
        }

        return redirect()
            ->route('students.index', ['programme_id' => $validated['programme_id']])
            ->with('success', 'Student registered with portal login access.');
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['programme', 'institution', 'user']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        $institution = $this->institution();
        abort_unless($student->institution_id === $institution->id, 404);

        $programmes = Programme::query()
            ->where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        $defaultEmailExample = $this->studentService->exampleEmail($institution);

        return view('students.edit', compact('student', 'programmes', 'institution', 'defaultEmailExample'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $institution = $this->institution();
        abort_unless($student->institution_id === $institution->id, 404);

        $validated = $request->validate([
            'programme_id' => [
                'required',
                Rule::exists('programmes', 'id')->where('institution_id', $institution->id),
            ],
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')
                    ->where('institution_id', $institution->id)
                    ->ignore($student->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')
                    ->where('institution_id', $institution->id)
                    ->ignore($student->id),
                $this->institutionUserEmailRule($institution->id, $student->user_id),
            ],
            'year_of_study' => 'required|integer|min:1|max:8',
            'status' => 'required|in:active,inactive,graduated,suspended',
            'password' => 'nullable|string|min:8',
        ]);

        $student->update([
            'programme_id' => $validated['programme_id'],
            'student_number' => $validated['student_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'year_of_study' => $validated['year_of_study'],
            'status' => $validated['status'],
        ]);

        if ($student->user) {
            $userData = [
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
            ];

            if (filled($validated['password'] ?? null)) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $student->user->update($userData);
        }

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $institution = $this->institution();
        abort_unless($student->institution_id === $institution->id, 404);

        $programmeId = $student->programme_id;

        if ($student->user) {
            $student->user->delete();
        }

        $student->delete();

        return redirect()
            ->route('students.index', ['programme_id' => $programmeId])
            ->with('success', 'Student removed.');
    }

    public function courses(Request $request, Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['programme', 'institution']);

        $period = $this->registrationService->resolveRegistrationPeriod(
            $student,
            $request->query('academic_year'),
            $request->query('semester') ? (int) $request->query('semester') : null
        );

        $enrolments = $student->courseEnrolments()
            ->with([
                'courseOffering.course',
                'courseOffering.lecturer',
                'result',
            ])
            ->whereHas('courseOffering', function ($q) use ($period) {
                $q->where('academic_year', $period['academic_year'])
                    ->where('semester', $period['semester']);
            })
            ->get()
            ->sortBy(fn ($e) => $e->courseOffering?->course?->code ?? '');

        $periodOptions = $student->courseEnrolments()
            ->with('courseOffering')
            ->get()
            ->map(fn ($e) => [
                'academic_year' => $e->courseOffering->academic_year,
                'semester' => $e->courseOffering->semester,
            ])
            ->unique(fn ($p) => $p['academic_year'].'-'.$p['semester'])
            ->prepend($period)
            ->unique(fn ($p) => $p['academic_year'].'-'.$p['semester'])
            ->sortByDesc(fn ($p) => $p['academic_year'].$p['semester'])
            ->values();

        return view('students.courses', compact('student', 'enrolments', 'period', 'periodOptions'));
    }
}
