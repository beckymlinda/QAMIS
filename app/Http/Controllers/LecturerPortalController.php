<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\LmsNotification;
use App\Services\LecturerPortalService;
use App\Services\LmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LecturerPortalController extends Controller
{
    public function __construct(
        protected LecturerPortalService $portalService,
        protected LmsService $lms,
    ) {}

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

    public function notifications(): View
    {
        $staff = $this->staff();
        $notifications = LmsNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('lecturer.notifications', compact('staff', 'notifications'));
    }

    public function readNotification(LmsNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markRead();

        return redirect($notification->link ?? route('lecturer.dashboard'));
    }
}
