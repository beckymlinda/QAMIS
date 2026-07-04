<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\CourseResult;
use App\Models\StudentCourseEnrolment;
use App\Support\GpaGrading;
use App\Services\LecturerPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturerPortalController extends Controller
{
    public function __construct(protected LecturerPortalService $portalService) {}

    protected function staff()
    {
        $staff = $this->portalService->staffProfile((int) auth()->id());

        abort_unless($staff, 403, 'No lecturer profile linked to this account.');

        return $staff->load('programme', 'institution');
    }

    public function dashboard(): View
    {
        $staff = $this->staff();
        $offerings = $this->portalService->offerings($staff);
        $upcomingSlots = $this->portalService->timetableSlots($staff)->take(5);
        $totalStudents = $offerings->sum(fn ($o) => $o->studentEnrolments->count());

        return view('lecturer.dashboard', compact('staff', 'offerings', 'upcomingSlots', 'totalStudents'));
    }

    public function courses(): View
    {
        $staff = $this->staff();
        $offerings = $this->portalService->offerings($staff);

        return view('lecturer.courses', compact('staff', 'offerings'));
    }

    public function timetable(): View
    {
        $staff = $this->staff();
        $slots = $this->portalService->timetableSlots($staff);

        return view('lecturer.timetable', compact('staff', 'slots'));
    }

    public function students(CourseOffering $offering): View
    {
        $staff = $this->staff();
        abort_unless($offering->staff_member_id === $staff->id, 403);

        $offering->load(['course', 'studentEnrolments.student', 'studentEnrolments.result']);

        return view('lecturer.students', compact('staff', 'offering'));
    }

    public function gradeForm(CourseOffering $offering): View
    {
        $staff = $this->staff();
        abort_unless($offering->staff_member_id === $staff->id, 403);

        $offering->load(['course', 'studentEnrolments.student', 'studentEnrolments.result']);

        return view('lecturer.grade', compact('staff', 'offering'));
    }

    public function storeGrades(Request $request, CourseOffering $offering): RedirectResponse
    {
        $staff = $this->staff();
        abort_unless($offering->staff_member_id === $staff->id, 403);

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.enrolment_id' => 'required|exists:student_course_enrolments,id',
            'grades.*.coursework_percentage' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam_percentage' => 'nullable|numeric|min:0|max:100',
            'publish' => 'nullable|boolean',
        ]);

        $publish = $request->boolean('publish');

        foreach ($validated['grades'] as $gradeData) {
            $enrolment = StudentCourseEnrolment::findOrFail($gradeData['enrolment_id']);
            abort_unless($enrolment->course_offering_id === $offering->id, 403);

            $coursework = isset($gradeData['coursework_percentage']) && $gradeData['coursework_percentage'] !== ''
                ? (float) $gradeData['coursework_percentage'] : null;
            $exam = isset($gradeData['exam_percentage']) && $gradeData['exam_percentage'] !== ''
                ? (float) $gradeData['exam_percentage'] : null;

            $final = GpaGrading::computeFinal($coursework, $exam);

            if ($final === null) {
                continue;
            }

            $band = GpaGrading::fromPercentage($final);

            CourseResult::updateOrCreate(
                ['student_course_enrolment_id' => $enrolment->id],
                [
                    'coursework_percentage' => $coursework,
                    'exam_percentage' => $exam,
                    'final_percentage' => $final,
                    'letter_grade' => $band['letter'],
                    'grade_points' => $band['points'],
                    'quality_label' => $band['quality'],
                    'academic_decision' => $band['decision'],
                    'is_published' => $publish,
                    'published_at' => $publish ? now() : null,
                    'graded_by_staff_member_id' => $staff->id,
                    'graded_at' => now(),
                ]
            );
        }

        $message = $publish
            ? 'Grades saved and published to students.'
            : 'Grades saved as draft. Publish when ready for students to view.';

        return redirect()->route('lecturer.offerings.students', $offering)->with('success', $message);
    }

    public function evaluations(): View
    {
        $staff = $this->staff();
        $summaries = $this->portalService->evaluationSummary($staff);

        return view('lecturer.evaluations', compact('staff', 'summaries'));
    }

    public function profile(): View
    {
        $staff = $this->staff();

        return view('lecturer.profile', compact('staff'));
    }
}
