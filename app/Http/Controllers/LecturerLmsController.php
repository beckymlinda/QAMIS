<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\LmsAnnouncement;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsDiscussion;
use App\Models\LmsMaterial;
use App\Models\LmsModule;
use App\Services\LecturerPortalService;
use App\Services\LmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LecturerLmsController extends Controller
{
    public function __construct(
        protected LecturerPortalService $portalService,
        protected LmsService $lms,
    ) {}

    protected function staff()
    {
        $staff = $this->portalService->staffProfile((int) auth()->id());
        abort_unless($staff, 403);

        return $staff;
    }

    protected function offering(CourseOffering $offering)
    {
        $staff = $this->staff();
        $this->lms->assertLecturerAccess($offering, $staff);

        return $offering->load(['course', 'lecturer']);
    }

    public function show(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $outline = $this->lms->outlineFor($offering);
        $announcements = $this->lms->publishedAnnouncements($offering, true)->take(5);
        $assignments = $this->lms->publishedAssignments($offering, true)->take(5);
        $analytics = $this->lms->offeringAnalytics($offering);

        return view('lms.lecturer.show', compact('offering', 'outline', 'announcements', 'assignments', 'analytics'));
    }

    public function outline(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $outlineItems = $this->lms->outlineItemsFor($offering);
        $groupedItems = $outlineItems->groupBy('type');

        return view('lms.lecturer.outline', compact('offering', 'groupedItems'));
    }

    public function storeOutlineItem(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(\App\Models\LmsOutlineItem::TYPES)),
            'title' => 'required|string|max:255',
            'body' => 'nullable|string|required_without:file',
            'file' => 'nullable|file|max:20480|required_without:body',
        ]);

        $maxOrder = $offering->lmsOutlineItems()->where('type', $validated['type'])->max('sort_order') ?? 0;
        $path = $request->hasFile('file')
            ? $this->lms->storeUpload($offering, $request->file('file'), 'outline')
            : null;

        $offering->lmsOutlineItems()->create([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'file_path' => $path,
            'sort_order' => $maxOrder + 1,
        ]);

        $this->lms->logActivity(auth()->user(), 'outline_item_created', $offering);

        return back()->with('success', 'Outline item added.');
    }

    public function updateOutlineItem(Request $request, CourseOffering $offering, \App\Models\LmsOutlineItem $item): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($item->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
            'remove_file' => 'boolean',
        ]);

        if ($request->boolean('remove_file')) {
            $this->lms->deleteUpload($item->file_path);
            $item->file_path = null;
        }

        if ($request->hasFile('file')) {
            $this->lms->deleteUpload($item->file_path);
            $item->file_path = $this->lms->storeUpload($offering, $request->file('file'), 'outline');
        }

        $item->update([
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'file_path' => $item->file_path,
        ]);

        return back()->with('success', 'Outline item updated.');
    }

    public function destroyOutlineItem(CourseOffering $offering, \App\Models\LmsOutlineItem $item): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($item->course_offering_id === $offering->id, 404);

        $this->lms->deleteUpload($item->file_path);
        $item->delete();

        return back()->with('success', 'Outline item deleted.');
    }

    public function downloadOutlineItem(CourseOffering $offering, \App\Models\LmsOutlineItem $item): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($item->course_offering_id === $offering->id, 404);
        abort_unless($item->file_path && Storage::disk('local')->exists($item->file_path), 404);

        return Storage::disk('local')->download($item->file_path, $item->title);
    }

    /** @deprecated Legacy bulk outline update — kept for compatibility */
    public function updateOutline(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);
        $outline = $this->lms->outlineFor($offering);

        $validated = $request->validate([
            'learning_outcomes' => 'nullable|string',
            'assessment_plan' => 'nullable|string',
            'weekly_schedule' => 'nullable|string',
        ]);

        $outline->update($validated);
        $this->lms->logActivity(auth()->user(), 'outline_updated', $offering);

        return redirect()->route('lecturer.lms.outline', $offering)->with('success', 'Course outline saved.');
    }

    public function content(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $modules = $this->lms->visibleModules($offering, true);

        return view('lms.lecturer.content', compact('offering', 'modules'));
    }

    public function storeModule(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $maxOrder = $offering->lmsModules()->max('sort_order') ?? 0;
        $offering->lmsModules()->create([
            ...$validated,
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Learning module added.');
    }

    public function updateModule(Request $request, CourseOffering $offering, LmsModule $module): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($module->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
            'visible_from' => 'nullable|date',
            'visible_until' => 'nullable|date|after_or_equal:visible_from',
        ]);

        $module->update([
            ...$validated,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Module updated.');
    }

    public function destroyModule(CourseOffering $offering, LmsModule $module): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($module->course_offering_id === $offering->id, 404);

        foreach ($module->materials as $material) {
            $this->lms->deleteUpload($material->file_path);
        }
        $module->delete();

        return back()->with('success', 'Module deleted.');
    }

    public function storeMaterial(Request $request, CourseOffering $offering, LmsModule $module): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($module->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_keys(\App\Models\LmsMaterial::TYPES)),
            'external_url' => 'nullable|url|max:2048',
            'file' => 'nullable|file|max:20480',
            'allow_download' => 'boolean',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $this->lms->storeUpload($offering, $request->file('file'), 'materials');
        }

        $maxOrder = $module->materials()->max('sort_order') ?? 0;
        $module->materials()->create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'file_path' => $path,
            'external_url' => $validated['external_url'] ?? null,
            'sort_order' => $maxOrder + 1,
            'allow_download' => $request->boolean('allow_download', true),
        ]);

        return back()->with('success', 'Learning material added.');
    }

    public function destroyMaterial(CourseOffering $offering, LmsMaterial $material): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($material->module->course_offering_id === $offering->id, 404);

        $this->lms->deleteUpload($material->file_path);
        $material->delete();

        return back()->with('success', 'Material removed.');
    }

    public function announcements(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $announcements = $this->lms->publishedAnnouncements($offering, true);

        return view('lms.lecturer.announcements', compact('offering', 'announcements'));
    }

    public function storeAnnouncement(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'publish_now' => 'boolean',
        ]);

        $announcement = $offering->lmsAnnouncements()->create([
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published_at' => $request->boolean('publish_now') ? now() : null,
        ]);

        if ($announcement->isPublished()) {
            $this->lms->notifyEnrolledStudents(
                $offering,
                'New announcement: '.$announcement->title,
                $announcement->body,
                route('student.lms.show', $offering)
            );
        }

        return back()->with('success', 'Announcement saved.');
    }

    public function publishAnnouncement(CourseOffering $offering, LmsAnnouncement $announcement): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($announcement->course_offering_id === $offering->id, 404);

        $announcement->update(['published_at' => now()]);
        $this->lms->notifyEnrolledStudents(
            $offering,
            'New announcement: '.$announcement->title,
            $announcement->body,
            route('student.lms.show', $offering)
        );

        return back()->with('success', 'Announcement published.');
    }

    public function destroyAnnouncement(CourseOffering $offering, LmsAnnouncement $announcement): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($announcement->course_offering_id === $offering->id, 404);
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    public function assignments(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $assignments = $this->lms->publishedAssignments($offering, true);

        return view('lms.lecturer.assignments', compact('offering', 'assignments'));
    }

    public function storeAssignment(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:1000',
            'allow_late' => 'boolean',
            'is_published' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $path = $request->hasFile('attachment')
            ? $this->lms->storeUpload($offering, $request->file('attachment'), 'assignments')
            : null;

        $assignment = $offering->lmsAssignments()->create([
            ...collect($validated)->except('attachment')->all(),
            'attachment_file_path' => $path,
            'allow_late' => $request->boolean('allow_late'),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($assignment->is_published) {
            $this->lms->notifyEnrolledStudents(
                $offering,
                'New assignment: '.$assignment->title,
                'Due: '.($assignment->due_at?->format('d M Y H:i') ?? 'No deadline'),
                route('student.lms.assignments.show', [$offering, $assignment])
            );
        }

        return back()->with('success', 'Assignment created.');
    }

    public function updateAssignment(Request $request, CourseOffering $offering, LmsAssignment $assignment): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:1000',
            'allow_late' => 'boolean',
            'is_published' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf|max:20480',
            'remove_attachment' => 'boolean',
        ]);

        if ($request->boolean('remove_attachment')) {
            $this->lms->deleteUpload($assignment->attachment_file_path);
            $assignment->attachment_file_path = null;
        }

        if ($request->hasFile('attachment')) {
            $this->lms->deleteUpload($assignment->attachment_file_path);
            $assignment->attachment_file_path = $this->lms->storeUpload($offering, $request->file('attachment'), 'assignments');
        }

        $assignment->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'max_score' => $validated['max_score'],
            'allow_late' => $request->boolean('allow_late'),
            'is_published' => $request->boolean('is_published'),
            'attachment_file_path' => $assignment->attachment_file_path,
        ]);

        return back()->with('success', 'Assignment updated.');
    }

    public function destroyAssignment(CourseOffering $offering, LmsAssignment $assignment): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id, 404);

        $this->lms->deleteUpload($assignment->attachment_file_path);
        foreach ($assignment->submissions as $submission) {
            $this->lms->deleteUpload($submission->file_path);
            $this->lms->deleteUpload($submission->marked_file_path);
        }
        $assignment->delete();

        return back()->with('success', 'Assignment deleted.');
    }

    public function downloadAssignmentAttachment(CourseOffering $offering, LmsAssignment $assignment): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id, 404);
        abort_unless($assignment->attachment_file_path && Storage::disk('local')->exists($assignment->attachment_file_path), 404);

        return Storage::disk('local')->download($assignment->attachment_file_path, $assignment->title.'.pdf');
    }

    public function submissions(CourseOffering $offering, LmsAssignment $assignment): View
    {
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id, 404);

        $assignment->load(['submissions.student']);

        return view('lms.lecturer.submissions', compact('offering', 'assignment'));
    }

    public function showSubmission(CourseOffering $offering, LmsAssignmentSubmission $submission): View
    {
        $offering = $this->offering($offering);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);

        $submission->load(['student', 'assignment']);
        $assignment = $submission->assignment;

        return view('lms.lecturer.submission-show', compact('offering', 'assignment', 'submission'));
    }

    public function gradeSubmission(Request $request, CourseOffering $offering, LmsAssignmentSubmission $submission): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:'.$submission->assignment->max_score,
            'feedback' => 'nullable|string',
            'annotation_data' => 'nullable|string',
            'marked_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $annotationData = isset($validated['annotation_data'])
            ? json_decode($validated['annotation_data'], true)
            : null;

        if ($request->hasFile('marked_file')) {
            $this->lms->deleteUpload($submission->marked_file_path);
            $submission->marked_file_path = $this->lms->storeUpload($offering, $request->file('marked_file'), 'marked-submissions');
        }

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'annotation_data' => $annotationData,
            'marked_file_path' => $submission->marked_file_path,
            'graded_at' => now(),
            'graded_by' => auth()->id(),
        ]);

        if ($submission->student?->user) {
            $body = 'Score: '.$validated['score'].'/'.$submission->assignment->max_score;
            if ($submission->hasMarkedFile()) {
                $body .= '. Marked copy available for download.';
            }
            $this->lms->notify(
                $submission->student->user,
                'Assignment graded: '.$submission->assignment->title,
                $body,
                route('student.lms.assignments.show', [$offering, $submission->assignment])
            );
        }

        return redirect()
            ->route('lecturer.lms.submissions.show', [$offering, $submission])
            ->with('success', 'Submission graded.');
    }

    public function downloadSubmissionFile(CourseOffering $offering, LmsAssignmentSubmission $submission): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path, 'submission-'.$submission->student->student_number);
    }

    public function previewSubmissionFile(CourseOffering $offering, LmsAssignmentSubmission $submission): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $offering = $this->offering($offering);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);

        return response()->file(Storage::disk('local')->path($submission->file_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="submission.pdf"',
        ]);
    }

    public function downloadMarkedSubmission(CourseOffering $offering, LmsAssignmentSubmission $submission): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($submission->assignment->course_offering_id === $offering->id, 404);
        abort_unless($submission->marked_file_path && Storage::disk('local')->exists($submission->marked_file_path), 404);

        return Storage::disk('local')->download($submission->marked_file_path, 'marked-'.$submission->student->student_number.'.pdf');
    }

    public function discussions(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $discussions = $offering->lmsDiscussions()->with(['author', 'posts'])->get();

        return view('lms.lecturer.discussions', compact('offering', 'discussions'));
    }

    public function storeDiscussion(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        $offering->lmsDiscussions()->create([
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return back()->with('success', 'Discussion topic created.');
    }

    public function showDiscussion(CourseOffering $offering, LmsDiscussion $discussion): View
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);

        $discussion->load(['author', 'posts.user', 'posts.replies.user']);

        return view('lms.lecturer.discussion-show', compact('offering', 'discussion'));
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

        return back()->with('success', 'Reply posted.');
    }

    public function analytics(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $analytics = $this->lms->offeringAnalytics($offering);

        return view('lms.lecturer.analytics', compact('offering', 'analytics'));
    }

    public function downloadMaterial(CourseOffering $offering, LmsMaterial $material): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($material->module->course_offering_id === $offering->id, 404);
        abort_unless($material->file_path && Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->title);
    }
}
