<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\LmsActivityLog;
use App\Models\LmsAnnouncement;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsCourseOutline;
use App\Models\LmsDiscussion;
use App\Models\LmsDiscussionPost;
use App\Models\LmsMaterial;
use App\Models\LmsModule;
use App\Models\LmsNotification;
use App\Models\LmsOutlineItem;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LmsService
{
    public function assertLecturerAccess(CourseOffering $offering, StaffMember $staff): void
    {
        abort_unless($offering->staff_member_id === $staff->id, 403);
    }

    public function assertStudentEnrolled(CourseOffering $offering, Student $student): void
    {
        abort_unless(
            $student->courseEnrolments()->where('course_offering_id', $offering->id)->exists(),
            403,
            'You are not enrolled in this course.'
        );
    }

    public function outlineFor(CourseOffering $offering): LmsCourseOutline
    {
        return $offering->lmsOutline()->firstOrCreate(['course_offering_id' => $offering->id]);
    }

    public function outlineItemsFor(CourseOffering $offering): Collection
    {
        $this->migrateLegacyOutlineItems($offering);

        return $offering->lmsOutlineItems()->orderBy('type')->orderBy('sort_order')->get();
    }

    public function migrateLegacyOutlineItems(CourseOffering $offering): void
    {
        if ($offering->lmsOutlineItems()->exists()) {
            return;
        }

        $outline = $this->outlineFor($offering);
        $map = [
            'learning_outcome' => $outline->learning_outcomes,
            'assessment_plan' => $outline->assessment_plan,
            'weekly_schedule' => $outline->weekly_schedule,
        ];

        foreach ($map as $type => $body) {
            if (filled($body)) {
                $offering->lmsOutlineItems()->create([
                    'type' => $type,
                    'title' => LmsOutlineItem::TYPES[$type],
                    'body' => $body,
                    'sort_order' => 1,
                ]);
            }
        }
    }

    public function visibleModules(CourseOffering $offering, bool $forLecturer = false): Collection
    {
        $modules = $offering->lmsModules()->with('materials')->get();

        if ($forLecturer) {
            return $modules;
        }

        return $modules->filter(fn ($module) => $module->isVisibleNow())->values();
    }

    public function publishedAnnouncements(CourseOffering $offering, bool $forLecturer = false): Collection
    {
        $query = $offering->lmsAnnouncements()->with('author');

        if (! $forLecturer) {
            $query->whereNotNull('published_at')->where('published_at', '<=', now());
        }

        return $query->get();
    }

    public function publishedAssignments(CourseOffering $offering, bool $forLecturer = false): Collection
    {
        $query = $offering->lmsAssignments()->withCount('submissions');

        if (! $forLecturer) {
            $query->where('is_published', true);
        }

        return $query->get();
    }

    public function notifyEnrolledStudents(CourseOffering $offering, string $title, string $body, string $link): void
    {
        $offering->loadMissing('studentEnrolments.student.user');

        foreach ($offering->studentEnrolments as $enrolment) {
            $user = $enrolment->student?->user;
            if ($user) {
                $this->notify($user, $title, $body, $link);
            }
        }
    }

    public function notifyOfferingLecturer(CourseOffering $offering, string $title, string $body, string $link): void
    {
        $offering->loadMissing('lecturer.user');
        $user = $offering->lecturer?->user;

        if ($user) {
            $this->notify($user, $title, $body, $link);
        }
    }

    public function notifyStudentsOfModuleContent(CourseOffering $offering, LmsModule $module, string $materialTitle): void
    {
        if (! $module->is_published || ! $module->isVisibleNow()) {
            return;
        }

        $this->notifyEnrolledStudents(
            $offering,
            'New learning material: '.$materialTitle,
            'Added to module: '.$module->title,
            route('student.lms.content', $offering)
        );
    }

    public function addMaterial(
        CourseOffering $offering,
        LmsModule $module,
        array $validated,
        $file = null,
        bool $allowDownload = true,
    ): LmsMaterial {
        $path = null;
        if ($file) {
            $path = $this->storeUpload($offering, $file, 'materials');
        }

        $maxOrder = $module->materials()->max('sort_order') ?? 0;

        return $module->materials()->create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'file_path' => $path,
            'external_url' => $validated['external_url'] ?? null,
            'sort_order' => $maxOrder + 1,
            'allow_download' => $allowDownload,
        ]);
    }

    public function notify(User $user, string $title, ?string $body = null, ?string $link = null): LmsNotification
    {
        return LmsNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);
    }

    public function logActivity(User $user, string $action, ?CourseOffering $offering = null, ?array $metadata = null): void
    {
        LmsActivityLog::create([
            'user_id' => $user->id,
            'course_offering_id' => $offering?->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    public function storeUpload(CourseOffering $offering, $file, string $folder): string
    {
        return $file->store("lms/{$offering->institution_id}/{$offering->id}/{$folder}", 'local');
    }

    public function deleteUpload(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function offeringAnalytics(CourseOffering $offering): array
    {
        $enrolledCount = $offering->studentEnrolments()->count();
        $assignments = $offering->lmsAssignments()->where('is_published', true)->get();
        $submissionCount = LmsAssignmentSubmission::query()
            ->whereIn('lms_assignment_id', $assignments->pluck('id'))
            ->whereNotNull('submitted_at')
            ->count();
        $gradedCount = LmsAssignmentSubmission::query()
            ->whereIn('lms_assignment_id', $assignments->pluck('id'))
            ->whereNotNull('graded_at')
            ->count();
        $discussionPosts = $offering->lmsDiscussions()->withCount('posts')->get()->sum('posts_count');
        $moduleCount = $offering->lmsModules()->where('is_published', true)->count();
        $materialCount = $offering->lmsModules()
            ->where('is_published', true)
            ->withCount('materials')
            ->get()
            ->sum('materials_count');

        $expectedSubmissions = $enrolledCount * max($assignments->count(), 1);
        $completionRate = $expectedSubmissions > 0
            ? round(($submissionCount / $expectedSubmissions) * 100, 1)
            : 0;

        return compact(
            'enrolledCount',
            'assignments',
            'submissionCount',
            'gradedCount',
            'discussionPosts',
            'moduleCount',
            'materialCount',
            'completionRate'
        );
    }

    public function studentProgress(Student $student, CourseOffering $offering): array
    {
        $assignments = $this->publishedAssignments($offering);
        $submissions = LmsAssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('lms_assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('lms_assignment_id');

        $pending = $assignments->filter(function (LmsAssignment $assignment) use ($submissions) {
            $submission = $submissions->get($assignment->id);

            return ! $submission || $submission->submitted_at === null;
        });

        $upcomingDeadlines = $assignments
            ->filter(fn (LmsAssignment $a) => $a->due_at && $a->due_at->isFuture())
            ->sortBy('due_at')
            ->take(5);

        return [
            'assignments' => $assignments,
            'submissions' => $submissions,
            'pendingCount' => $pending->count(),
            'upcomingDeadlines' => $upcomingDeadlines,
        ];
    }

    public function unreadNotifications(User $user): int
    {
        return LmsNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function discussionMessages(LmsDiscussion $discussion): Collection
    {
        $opening = collect([[
            'key' => 'opening',
            'post_id' => null,
            'user' => $discussion->author,
            'body' => $discussion->body,
            'created_at' => $discussion->created_at,
            'file_path' => null,
            'file_name' => null,
        ]]);

        $posts = $discussion->posts->map(fn (LmsDiscussionPost $post) => [
            'key' => 'post-'.$post->id,
            'post_id' => $post->id,
            'user' => $post->user,
            'body' => $post->body,
            'created_at' => $post->created_at,
            'file_path' => $post->file_path,
            'file_name' => $post->file_name,
        ]);

        return $opening->concat($posts)->sortBy('created_at')->values();
    }

    public function storeDiscussionPost(
        CourseOffering $offering,
        LmsDiscussion $discussion,
        User $user,
        ?string $body,
        $file = null,
    ): LmsDiscussionPost {
        abort_if($discussion->is_closed, 403, 'This discussion is closed.');
        abort_unless($body || $file, 422, 'Write a message or attach a file.');

        $path = null;
        $name = null;
        if ($file) {
            $path = $this->storeUpload($offering, $file, 'discussions');
            $name = $file->getClientOriginalName();
        }

        return $discussion->posts()->create([
            'user_id' => $user->id,
            'body' => $body ?? '',
            'file_path' => $path,
            'file_name' => $name,
        ]);
    }

    public function enrolmentIds(CourseOffering $offering): Collection
    {
        return StudentCourseEnrolment::query()
            ->where('course_offering_id', $offering->id)
            ->pluck('student_id');
    }
}
