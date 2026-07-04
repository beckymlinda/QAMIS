<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsDiscussion;
use App\Models\LmsMaterial;
use App\Models\LmsNotification;
use App\Services\LmsService;
use App\Services\StudentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentLmsController extends Controller
{
    public function __construct(
        protected StudentPortalService $portalService,
        protected LmsService $lms,
    ) {}

    protected function student()
    {
        $student = auth()->user()?->studentProfile;
        abort_unless($student, 403);

        return $student->load('programme', 'institution');
    }

    protected function offering(CourseOffering $offering)
    {
        $student = $this->student();
        $this->lms->assertStudentEnrolled($offering, $student);

        return $offering->load(['course', 'lecturer']);
    }

    public function show(CourseOffering $offering): View
    {
        $student = $this->student();
        $offering = $this->offering($offering);
        $outline = $this->lms->outlineFor($offering);
        $announcements = $this->lms->publishedAnnouncements($offering)->take(5);
        $progress = $this->lms->studentProgress($student, $offering);
        $modules = $this->lms->visibleModules($offering);

        return view('lms.student.show', compact('student', 'offering', 'outline', 'announcements', 'progress', 'modules'));
    }

    public function content(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $modules = $this->lms->visibleModules($offering);

        return view('lms.student.content', compact('offering', 'modules'));
    }

    public function assignments(CourseOffering $offering): View
    {
        $student = $this->student();
        $offering = $this->offering($offering);
        $progress = $this->lms->studentProgress($student, $offering);

        return view('lms.student.assignments', compact('student', 'offering', 'progress'));
    }

    public function showAssignment(CourseOffering $offering, LmsAssignment $assignment): View
    {
        $student = $this->student();
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id && $assignment->is_published, 404);

        $submission = LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('lms.student.assignment-show', compact('student', 'offering', 'assignment', 'submission'));
    }

    public function submitAssignment(Request $request, CourseOffering $offering, LmsAssignment $assignment): RedirectResponse
    {
        $student = $this->student();
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id && $assignment->is_published, 404);
        abort_unless($assignment->isOpenForSubmission(), 403, 'This assignment is closed.');

        $validated = $request->validate([
            'body' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);

        abort_unless($validated['body'] || $request->hasFile('file'), 422, 'Provide a written response or upload a file.');

        $submission = LmsAssignmentSubmission::query()->firstOrNew([
            'lms_assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        abort_if($submission->exists && $submission->submitted_at !== null, 403, 'You have already submitted this assignment.');

        if ($request->hasFile('file')) {
            $this->lms->deleteUpload($submission->file_path);
            $submission->file_path = $this->lms->storeUpload($offering, $request->file('file'), 'submissions');
        }

        $submission->body = $validated['body'] ?? $submission->body;
        $submission->submitted_at = now();
        $submission->save();

        $this->lms->logActivity(auth()->user(), 'assignment_submitted', $offering, [
            'assignment_id' => $assignment->id,
        ]);

        return redirect()->route('student.lms.assignments.show', [$offering, $assignment])
            ->with('success', 'Assignment submitted successfully.');
    }

    public function discussions(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $discussions = $offering->lmsDiscussions()->with(['author', 'posts'])->get();

        return view('lms.student.discussions', compact('offering', 'discussions'));
    }

    public function showDiscussion(CourseOffering $offering, LmsDiscussion $discussion): View
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);

        $discussion->load(['author', 'posts.user', 'posts.replies.user']);

        return view('lms.student.discussion-show', compact('offering', 'discussion'));
    }

    public function storeDiscussionPost(Request $request, CourseOffering $offering, LmsDiscussion $discussion): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:lms_discussion_posts,id',
        ]);

        $discussion->posts()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $this->lms->logActivity(auth()->user(), 'discussion_post', $offering, [
            'discussion_id' => $discussion->id,
        ]);

        return back()->with('success', 'Reply posted.');
    }

    public function storeDiscussion(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $offering->lmsDiscussions()->create([
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Discussion topic started.');
    }

    public function notifications(): View
    {
        $student = $this->student();
        $notifications = LmsNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('lms.student.notifications', compact('student', 'notifications'));
    }

    public function readNotification(LmsNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markRead();

        return redirect($notification->link ?? route('student.dashboard'));
    }

    public function downloadMaterial(CourseOffering $offering, LmsMaterial $material): StreamedResponse
    {
        $this->offering($offering);
        abort_unless($material->module->course_offering_id === $offering->id, 404);
        abort_unless($material->allow_download, 403);
        abort_unless($material->file_path && Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->title);
    }

    public function downloadMarkedSubmission(CourseOffering $offering, LmsAssignmentSubmission $submission): StreamedResponse
    {
        $student = $this->student();
        $offering = $this->offering($offering);
        abort_unless($submission->student_id === $student->id, 403);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);
        abort_unless($submission->isGraded() && $submission->hasMarkedFile(), 404);
        abort_unless(Storage::disk('local')->exists($submission->marked_file_path), 404);

        return Storage::disk('local')->download($submission->marked_file_path, 'marked-assignment.pdf');
    }
}
