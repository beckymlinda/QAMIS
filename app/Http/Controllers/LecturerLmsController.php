<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\CourseResult;
use App\Models\LmsAnnouncement;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsDiscussion;
use App\Models\LmsDiscussionPost;
use App\Models\LmsMaterial;
use App\Models\LmsModule;
use App\Models\StudentCourseEnrolment;
use App\Services\CourseGradeCalculator;
use App\Services\LecturerPortalService;
use App\Services\LmsService;
use App\Support\GpaGrading;
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
        protected CourseGradeCalculator $grades,
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

    public function showModule(CourseOffering $offering, LmsModule $module): View
    {
        $offering = $this->offering($offering);
        abort_unless($module->course_offering_id === $offering->id, 404);

        $module->load('materials');
        $addMaterial = request()->boolean('addMaterial');

        return view('lms.lecturer.module-show', compact('offering', 'module', 'addMaterial'));
    }

    public function storeModule(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
            'material_title' => 'required|string|max:255',
            'material_type' => 'required|in:'.implode(',', array_keys(\App\Models\LmsMaterial::TYPES)),
            'material_external_url' => 'nullable|url|max:2048',
            'material_file' => 'nullable|file|max:20480',
            'allow_download' => 'boolean',
        ]);

        $maxOrder = $offering->lmsModules()->max('sort_order') ?? 0;
        $module = $offering->lmsModules()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $maxOrder + 1,
        ]);

        $material = $this->lms->addMaterial(
            $offering,
            $module,
            [
                'title' => $validated['material_title'],
                'type' => $validated['material_type'],
                'external_url' => $validated['material_external_url'] ?? null,
            ],
            $request->file('material_file'),
            $request->boolean('allow_download', true),
        );

        $this->lms->notifyStudentsOfModuleContent($offering, $module, $material->title);

        return redirect()
            ->route('lecturer.lms.modules.show', [$offering, $module])
            ->with('success', 'Learning module and first material added.');
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

        $wasPublished = $module->is_published;

        $module->update([
            ...$validated,
            'is_published' => $request->boolean('is_published'),
        ]);

        if (! $wasPublished && $module->is_published) {
            $module->load('materials');
            $firstMaterial = $module->materials->first();
            if ($firstMaterial) {
                $this->lms->notifyStudentsOfModuleContent($offering, $module, $firstMaterial->title);
            }
        }

        return redirect()
            ->route('lecturer.lms.modules.show', [$offering, $module])
            ->with('success', 'Module updated.');
    }

    public function destroyModule(CourseOffering $offering, LmsModule $module): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($module->course_offering_id === $offering->id, 404);

        foreach ($module->materials as $material) {
            $this->lms->deleteUpload($material->file_path);
        }
        $module->delete();

        return redirect()
            ->route('lecturer.lms.content', $offering)
            ->with('success', 'Module deleted.');
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

        $material = $this->lms->addMaterial(
            $offering,
            $module,
            $validated,
            $request->file('file'),
            $request->boolean('allow_download', true),
        );

        $this->lms->notifyStudentsOfModuleContent($offering, $module, $material->title);

        return redirect()
            ->route('lecturer.lms.modules.show', [$offering, $module])
            ->with('success', 'Learning material added.');
    }

    public function destroyMaterial(CourseOffering $offering, LmsMaterial $material): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($material->module->course_offering_id === $offering->id, 404);

        $this->lms->deleteUpload($material->file_path);
        $material->delete();

        $module = $material->module;

        return redirect()
            ->route('lecturer.lms.modules.show', [$offering, $module])
            ->with('success', 'Material removed.');
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

    public function showAnnouncement(CourseOffering $offering, LmsAnnouncement $announcement): View
    {
        $offering = $this->offering($offering);
        abort_unless($announcement->course_offering_id === $offering->id, 404);

        $announcement->load('author');

        return view('lms.lecturer.announcement-show', compact('offering', 'announcement'));
    }

    public function updateAnnouncement(Request $request, CourseOffering $offering, LmsAnnouncement $announcement): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($announcement->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'publish_now' => 'boolean',
        ]);

        $wasPublished = $announcement->isPublished();

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published_at' => $request->boolean('publish_now') ? ($announcement->published_at ?? now()) : null,
        ]);

        if (! $wasPublished && $announcement->isPublished()) {
            $this->lms->notifyEnrolledStudents(
                $offering,
                'New announcement: '.$announcement->title,
                $announcement->body,
                route('student.lms.show', $offering)
            );
        }

        return redirect()
            ->route('lecturer.lms.announcements', $offering)
            ->with('success', 'Announcement updated.');
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
        $courseworkUsed = $this->grades->usedCourseworkWeight($offering);
        $courseworkRemaining = $this->grades->remainingCourseworkWeight($offering);

        return view('lms.lecturer.assignments', compact('offering', 'assignments', 'courseworkUsed', 'courseworkRemaining'));
    }

    public function showAssignment(CourseOffering $offering, LmsAssignment $assignment): View
    {
        $offering = $this->offering($offering);
        abort_unless($assignment->course_offering_id === $offering->id, 404);

        $assignment->loadCount('submissions');

        return view('lms.lecturer.assignment-show', compact('offering', 'assignment'));
    }

    public function storeAssignment(Request $request, CourseOffering $offering): RedirectResponse
    {
        $offering = $this->offering($offering);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:1000',
            'coursework_weight_percent' => 'required|numeric|min:0|max:'.CourseGradeCalculator::COURSEWORK_PORTION_PERCENT,
            'allow_late' => 'boolean',
            'is_published' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $weight = (float) $validated['coursework_weight_percent'];
        if ($message = $this->grades->validateAssignmentWeight($offering, $weight)) {
            return back()->withInput()->withErrors(['coursework_weight_percent' => $message]);
        }

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
            'coursework_weight_percent' => 'required|numeric|min:0|max:'.CourseGradeCalculator::COURSEWORK_PORTION_PERCENT,
            'allow_late' => 'boolean',
            'is_published' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf|max:20480',
            'remove_attachment' => 'boolean',
        ]);

        $weight = (float) $validated['coursework_weight_percent'];
        if ($message = $this->grades->validateAssignmentWeight($offering, $weight, $assignment->id)) {
            return back()->withInput()->withErrors(['coursework_weight_percent' => $message]);
        }

        if ($request->boolean('remove_attachment')) {
            $this->lms->deleteUpload($assignment->attachment_file_path);
            $assignment->attachment_file_path = null;
        }

        if ($request->hasFile('attachment')) {
            $this->lms->deleteUpload($assignment->attachment_file_path);
            $assignment->attachment_file_path = $this->lms->storeUpload($offering, $request->file('attachment'), 'assignments');
        }

        $wasPublished = $assignment->is_published;

        $assignment->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'max_score' => $validated['max_score'],
            'coursework_weight_percent' => $weight,
            'allow_late' => $request->boolean('allow_late'),
            'is_published' => $request->boolean('is_published'),
            'attachment_file_path' => $assignment->attachment_file_path,
        ]);

        if (! $wasPublished && $assignment->is_published) {
            $this->lms->notifyEnrolledStudents(
                $offering,
                'New assignment: '.$assignment->title,
                'Due: '.($assignment->due_at?->format('d M Y H:i') ?? 'No deadline'),
                route('student.lms.assignments.show', [$offering, $assignment])
            );
        }

        $this->resyncOfferingGrades($offering);

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

        $enrolment = StudentCourseEnrolment::query()
            ->where('course_offering_id', $offering->id)
            ->where('student_id', $submission->student_id)
            ->first();

        if ($enrolment) {
            $this->grades->syncResult($enrolment, $offering, $this->staff());
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

        $discussion = $offering->lmsDiscussions()->create([
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()
            ->route('lecturer.lms.discussions.show', [$offering, $discussion])
            ->with('success', 'Discussion topic created.');
    }

    public function updateDiscussion(Request $request, CourseOffering $offering, LmsDiscussion $discussion): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);
        abort_unless($discussion->isCreator(auth()->user()), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        $discussion->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()
            ->route('lecturer.lms.discussions.show', [$offering, $discussion])
            ->with('success', 'Discussion updated.');
    }

    public function closeDiscussion(CourseOffering $offering, LmsDiscussion $discussion): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);
        abort_unless($discussion->isCreator(auth()->user()), 403);

        $discussion->update([
            'is_closed' => true,
            'closed_at' => now(),
        ]);

        return redirect()
            ->route('lecturer.lms.discussions.show', [$offering, $discussion])
            ->with('success', 'Discussion closed. No new replies can be posted.');
    }

    public function destroyDiscussion(CourseOffering $offering, LmsDiscussion $discussion): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);
        abort_unless($discussion->isCreator(auth()->user()), 403);

        foreach ($discussion->posts as $post) {
            $this->lms->deleteUpload($post->file_path);
        }
        $discussion->posts()->delete();
        $discussion->delete();

        return redirect()
            ->route('lecturer.lms.discussions', $offering)
            ->with('success', 'Discussion topic deleted.');
    }

    public function showDiscussion(CourseOffering $offering, LmsDiscussion $discussion): View
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);

        $discussion->load(['author', 'posts.user']);

        $messages = $this->lms->discussionMessages($discussion);
        $isCreator = $discussion->isCreator(auth()->user());

        return view('lms.lecturer.discussion-show', compact('offering', 'discussion', 'messages', 'isCreator'));
    }

    public function storeDiscussionPost(Request $request, CourseOffering $offering, LmsDiscussion $discussion): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($discussion->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'body' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:20480',
        ]);

        $this->lms->storeDiscussionPost(
            $offering,
            $discussion,
            auth()->user(),
            $validated['body'] ?? null,
            $request->file('file'),
        );

        return back()->with('success', 'Message sent.');
    }

    public function downloadDiscussionPostFile(CourseOffering $offering, LmsDiscussionPost $post): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($post->discussion->course_offering_id === $offering->id, 404);
        abort_unless($post->file_path && Storage::disk('local')->exists($post->file_path), 404);

        return Storage::disk('local')->download($post->file_path, $post->file_name ?? 'attachment');
    }

    public function analytics(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $analytics = $this->lms->offeringAnalytics($offering);

        return view('lms.lecturer.analytics', compact('offering', 'analytics'));
    }

    public function grades(CourseOffering $offering): View
    {
        $offering = $this->offering($offering);
        $gradebook = $this->grades->gradebook($offering);
        $courseworkUsed = $this->grades->usedCourseworkWeight($offering);
        $courseworkRemaining = $this->grades->remainingCourseworkWeight($offering);

        return view('lms.lecturer.grades', compact('offering', 'gradebook', 'courseworkUsed', 'courseworkRemaining'));
    }

    public function showGrade(CourseOffering $offering, StudentCourseEnrolment $enrolment): View
    {
        $offering = $this->offering($offering);
        abort_unless($enrolment->course_offering_id === $offering->id, 404);

        $enrolment->load(['student', 'result']);
        $breakdown = $this->grades->breakdown($enrolment, $offering);
        $result = $enrolment->result;
        $summary = $this->grades->gradeSummary($breakdown, $result);
        $semesterGpa = $this->grades->semesterGpaForStudent($enrolment->student, $offering);

        return view('lms.lecturer.grade-show', compact(
            'offering',
            'enrolment',
            'breakdown',
            'result',
            'summary',
            'semesterGpa',
        ));
    }

    public function updateGrade(Request $request, CourseOffering $offering, StudentCourseEnrolment $enrolment): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($enrolment->course_offering_id === $offering->id, 404);

        $validated = $request->validate([
            'exam_percentage' => 'nullable|numeric|min:0|max:100',
            'use_final_override' => 'boolean',
            'final_percentage_override' => 'nullable|required_if:use_final_override,1|numeric|min:0|max:100',
            'publish' => 'boolean',
        ]);

        $result = $enrolment->result ?? new CourseResult([
            'student_course_enrolment_id' => $enrolment->id,
        ]);

        $result->exam_percentage = isset($validated['exam_percentage']) && $validated['exam_percentage'] !== ''
            ? (float) $validated['exam_percentage']
            : null;
        $result->use_final_override = $request->boolean('use_final_override');
        $result->final_percentage_override = $result->use_final_override
            ? (float) $validated['final_percentage_override']
            : null;

        $publish = $request->boolean('publish');
        $result->is_published = $publish;
        $result->published_at = $publish ? now() : null;

        $result->save();

        $this->grades->syncResult($enrolment->fresh(), $offering, $this->staff());

        $message = $publish
            ? 'Grade saved and published to the student.'
            : 'Grade saved.';

        return redirect()
            ->route('lecturer.lms.grades.show', [$offering, $enrolment])
            ->with('success', $message);
    }

    public function destroyGrade(CourseOffering $offering, StudentCourseEnrolment $enrolment): RedirectResponse
    {
        $offering = $this->offering($offering);
        abort_unless($enrolment->course_offering_id === $offering->id, 404);

        $enrolment->result?->delete();

        return redirect()
            ->route('lecturer.lms.grades', $offering)
            ->with('success', 'Recorded grade removed for this student.');
    }

    protected function resyncOfferingGrades(CourseOffering $offering): void
    {
        $staff = $this->staff();

        foreach ($offering->studentEnrolments as $enrolment) {
            if ($enrolment->result) {
                $this->grades->syncResult($enrolment, $offering, $staff);
            }
        }
    }

    public function downloadMaterial(CourseOffering $offering, LmsMaterial $material): StreamedResponse
    {
        $offering = $this->offering($offering);
        abort_unless($material->module->course_offering_id === $offering->id, 404);
        abort_unless($material->file_path && Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->title);
    }
}
