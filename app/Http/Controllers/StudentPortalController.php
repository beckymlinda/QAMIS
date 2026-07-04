<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\EvaluationQuestion;
use App\Models\TeachingEvaluation;
use App\Models\TeachingEvaluationResponse;
use App\Services\StudentCourseRegistrationService;
use App\Services\StudentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function __construct(
        protected StudentPortalService $portalService,
        protected StudentCourseRegistrationService $registrationService,
    ) {}

    protected function student()
    {
        $student = auth()->user()?->studentProfile;

        abort_unless($student, 403, 'No student profile linked to this account.');

        return $student->load('programme', 'institution');
    }

    public function dashboard(): View
    {
        $student = $this->student();
        $period = $this->portalService->activeEvaluationPeriod($student);
        $evaluationItems = $this->portalService->evaluationItems($student, $period);
        $pendingCount = $evaluationItems->where('status', 'pending')->count();
        $upcomingSlots = $this->portalService->timetableSlots($student)->take(5);

        return view('student.dashboard', compact('student', 'period', 'evaluationItems', 'pendingCount', 'upcomingSlots'));
    }

    public function profile(): View
    {
        $student = $this->student();

        return view('student.profile', compact('student'));
    }

    public function timetable(): View
    {
        $student = $this->student();
        $slots = $this->portalService->timetableSlots($student);
        $dayNames = \App\Models\TimetableSlot::dayNames();

        return view('student.timetable', compact('student', 'slots', 'dayNames'));
    }

    public function evaluations(): View
    {
        $student = $this->student();
        $period = $this->portalService->activeEvaluationPeriod($student);
        $items = $this->portalService->evaluationItems($student, $period);

        return view('student.evaluations.index', compact('student', 'period', 'items'));
    }

    public function showEvaluation(CourseOffering $offering): View|RedirectResponse
    {
        $student = $this->student();
        $period = $this->portalService->activeEvaluationPeriod($student);

        abort_unless($period, 403, 'No evaluation period is currently open.');
        abort_unless(
            $student->courseEnrolments()->where('course_offering_id', $offering->id)->exists(),
            403,
            'You are not enrolled in this course.'
        );

        $offering->load(['course', 'lecturer']);

        $evaluation = TeachingEvaluation::firstOrCreate(
            [
                'student_id' => $student->id,
                'course_offering_id' => $offering->id,
                'evaluation_period_id' => $period->id,
            ],
            ['status' => 'draft']
        );

        if ($evaluation->isSubmitted()) {
            return redirect()->route('student.evaluations')->with('success', 'You have already submitted this evaluation.');
        }

        $questions = $this->portalService->evaluationQuestions();
        $existingResponses = $evaluation->responses()->pluck('rating', 'evaluation_question_id')
            ->merge($evaluation->responses()->pluck('response_text', 'evaluation_question_id'));

        return view('student.evaluations.form', compact(
            'student', 'offering', 'period', 'evaluation', 'questions', 'existingResponses'
        ));
    }

    public function submitEvaluation(Request $request, CourseOffering $offering): RedirectResponse
    {
        $student = $this->student();
        $period = $this->portalService->activeEvaluationPeriod($student);

        abort_unless($period?->isOpen(), 403, 'Evaluation period is closed.');
        abort_unless(
            $student->courseEnrolments()->where('course_offering_id', $offering->id)->exists(),
            403
        );

        $evaluation = TeachingEvaluation::query()
            ->where('student_id', $student->id)
            ->where('course_offering_id', $offering->id)
            ->where('evaluation_period_id', $period->id)
            ->firstOrFail();

        if ($evaluation->isSubmitted()) {
            return redirect()->route('student.evaluations');
        }

        $likertQuestions = EvaluationQuestion::where('question_type', 'likert_5')->pluck('id');
        $textQuestions = EvaluationQuestion::where('question_type', 'text')->pluck('id');

        $rules = ['responses' => 'required|array'];
        foreach ($likertQuestions as $questionId) {
            $rules["responses.{$questionId}"] = 'required|integer|min:1|max:5';
        }
        foreach ($textQuestions as $questionId) {
            $rules["responses.{$questionId}"] = 'nullable|string|max:2000';
        }

        $validated = $request->validate($rules);

        foreach ($validated['responses'] as $questionId => $value) {
            $question = EvaluationQuestion::find($questionId);
            if (! $question) {
                continue;
            }

            TeachingEvaluationResponse::updateOrCreate(
                [
                    'teaching_evaluation_id' => $evaluation->id,
                    'evaluation_question_id' => $questionId,
                ],
                $question->isLikert()
                    ? ['rating' => (int) $value, 'response_text' => null]
                    : ['rating' => null, 'response_text' => $value]
            );
        }

        $evaluation->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.evaluations')->with('success', 'Thank you. Your evaluation has been submitted anonymously.');
    }

    public function courses(Request $request): View
    {
        $student = $this->student();

        $period = $this->registrationService->resolveRegistrationPeriod(
            $student,
            $request->query('academic_year'),
            $request->query('semester') ? (int) $request->query('semester') : null
        );

        $enrolled = $this->registrationService->enrolledOfferings(
            $student,
            $period['academic_year'],
            $period['semester']
        );

        $available = $this->registrationService->registerableOfferings(
            $student,
            $period['academic_year'],
            $period['semester']
        );

        $lockedOfferingIds = $student->courseEnrolments()
            ->whereHas('result')
            ->pluck('course_offering_id');

        return view('student.courses', compact('student', 'enrolled', 'available', 'period', 'lockedOfferingIds'));
    }

    public function registerCourse(Request $request): RedirectResponse
    {
        $student = $this->student();

        $validated = $request->validate([
            'course_offering_id' => 'required|exists:course_offerings,id',
            'academic_year' => 'nullable|string|max:20',
            'semester' => 'nullable|integer|in:1,2',
        ]);

        $offering = CourseOffering::query()
            ->where('institution_id', $student->institution_id)
            ->findOrFail($validated['course_offering_id']);

        $this->registrationService->register($student, $offering);

        $period = $this->registrationService->resolveRegistrationPeriod(
            $student,
            $validated['academic_year'] ?? null,
            isset($validated['semester']) ? (int) $validated['semester'] : null
        );

        return redirect()
            ->route('student.courses', [
                'academic_year' => $period['academic_year'],
                'semester' => $period['semester'],
            ])
            ->with('success', 'Course registered successfully.');
    }

    public function dropCourse(Request $request, CourseOffering $offering): RedirectResponse
    {
        $student = $this->student();

        abort_unless($offering->institution_id === $student->institution_id, 404);

        $enrolment = $student->courseEnrolments()
            ->where('course_offering_id', $offering->id)
            ->firstOrFail();

        abort_if($enrolment->result()->exists(), 403, 'Cannot drop a course that has been graded.');

        $enrolment->delete();

        $period = $this->registrationService->resolveRegistrationPeriod(
            $student,
            $request->input('academic_year'),
            $request->input('semester') ? (int) $request->input('semester') : null
        );

        return redirect()
            ->route('student.courses', [
                'academic_year' => $period['academic_year'],
                'semester' => $period['semester'],
            ])
            ->with('success', 'Course registration removed.');
    }

    public function examResults(Request $request): View
    {
        $student = $this->student();
        $periods = $this->portalService->availableResultPeriods($student);

        $academicYear = $request->query('academic_year', $periods->first()['academic_year'] ?? null);
        $semester = $request->query('semester', $periods->first()['semester'] ?? null);

        $results = ($academicYear && $semester)
            ? $this->portalService->publishedResults($student, $academicYear, (int) $semester)
            : collect();

        $semesterGpa = $this->portalService->semesterGpa($results);

        return view('student.exam-results', compact(
            'student', 'periods', 'academicYear', 'semester', 'results', 'semesterGpa'
        ));
    }
}
