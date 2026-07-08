@php
    use App\Support\GpaGrading;
    use Illuminate\Support\Facades\Storage;

    $offering->loadMissing('course');

    $formatFileSize = function (?int $bytes): string {
        if (! $bytes) {
            return '—';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    };

    $fileMeta = function (?string $path) use ($formatFileSize): array {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return ['name' => null, 'type' => null, 'size' => null];
        }

        return [
            'name' => basename($path),
            'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION) ?: 'FILE'),
            'size' => $formatFileSize(Storage::disk('local')->size($path)),
        ];
    };

    $assignmentFile = $assignment->hasAttachment()
        ? $fileMeta($assignment->attachment_file_path)
        : ['name' => null, 'type' => null, 'size' => null];

    $submissionFile = $submission?->hasSubmissionFile()
        ? $fileMeta($submission->file_path)
        : ['name' => null, 'type' => null, 'size' => null];

    $isLate = $submission?->submitted_at
        && $assignment->due_at
        && $submission->submitted_at->gt($assignment->due_at);

    if ($submission?->isGraded()) {
        $statusLabel = 'Graded';
        $statusClasses = 'bg-emerald-50 text-emerald-800 ring-emerald-200';
        $statusIcon = 'bi-check-circle-fill';
    } elseif ($submission?->submitted_at) {
        $statusLabel = $isLate ? 'Late' : 'Submitted';
        $statusClasses = $isLate
            ? 'bg-orange-50 text-orange-800 ring-orange-200'
            : 'bg-blue-50 text-blue-800 ring-blue-200';
        $statusIcon = $isLate ? 'bi-exclamation-circle-fill' : 'bi-cloud-upload-fill';
    } else {
        $statusLabel = 'Not Submitted';
        $statusClasses = 'bg-gray-100 text-gray-700 ring-gray-200';
        $statusIcon = 'bi-circle';
    }

    $canSubmit = $assignment->isOpenForSubmission() && ! $submission?->submitted_at;
    $percentage = ($submission?->isGraded() && $assignment->max_score > 0)
        ? round(($submission->score / $assignment->max_score) * 100, 1)
        : null;
    $gradeBand = $percentage !== null ? GpaGrading::fromPercentage($percentage) : null;
    $passed = $gradeBand ? GpaGrading::hasPassed($gradeBand['letter']) : null;
    $passTone = $passed === true ? 'success' : ($passed === false ? 'fail' : 'neutral');

    $maxUploadMb = 20;
    $acceptedTypes = 'PDF, DOC, DOCX';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Assignment workspace</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $assignment->title }}</h2>
                <p class="mt-0.5 text-sm text-gray-500">{{ $offering->course->code }} · {{ $offering->course->title }}</p>
            </div>
            <a href="{{ route('student.lms.assignments', $offering) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#0f2744] transition hover:text-[#8cc63f]">
                <i class="bi bi-arrow-left"></i> Back to assignments
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ showSubmitForm: false }" @open-submit-form.window="showSubmitForm = true; $nextTick(() => document.getElementById('your-submission-card')?.scrollIntoView({ behavior: 'smooth' }))">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'student'])

        {{-- 1. Assignment Overview --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
            <div class="border-b border-gray-100 bg-gradient-to-r from-[#0f2744]/5 to-[#8cc63f]/10 px-6 py-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-2xl font-bold tracking-tight text-[#0f2744]">{{ $assignment->title }}</h3>
                        <p class="mt-1 flex items-center gap-2 text-sm text-gray-600">
                            <i class="bi bi-journal-bookmark text-[#8cc63f]"></i>
                            {{ $offering->course->title }}
                            <span class="text-gray-300">|</span>
                            <span class="font-mono text-xs text-gray-500">{{ $offering->course->code }}</span>
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide ring-1 {{ $statusClasses }}">
                        <i class="bi {{ $statusIcon }}"></i> {{ $statusLabel }}
                    </span>
                </div>
            </div>
            <div class="grid gap-4 px-6 py-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-gray-50/80 p-4 ring-1 ring-gray-100 transition hover:bg-gray-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Due date</p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-[#0f2744]">
                        <i class="bi bi-calendar-event text-amber-600"></i>
                        {{ $assignment->due_at?->format('d M Y, H:i') ?? 'No deadline' }}
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50/80 p-4 ring-1 ring-gray-100 transition hover:bg-gray-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Maximum grade</p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-[#0f2744]">
                        <i class="bi bi-award text-blue-600"></i>
                        {{ $assignment->max_score }} points
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50/80 p-4 ring-1 ring-gray-100 transition hover:bg-gray-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Coursework weight</p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-[#0f2744]">
                        <i class="bi bi-pie-chart text-purple-600"></i>
                        {{ $assignment->coursework_weight_percent ?? '—' }}{{ isset($assignment->coursework_weight_percent) ? '%' : '' }}
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50/80 p-4 ring-1 ring-gray-100 transition hover:bg-gray-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Late submissions</p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-[#0f2744]">
                        <i class="bi bi-clock-history text-gray-600"></i>
                        {{ $assignment->allow_late ? 'Allowed' : 'Not allowed' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- 2. Assignment Instructions --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <i class="bi bi-file-text text-lg text-[#0f2744]"></i>
                        <h3 class="text-base font-bold text-[#0f2744]">Assignment Instructions</h3>
                    </div>
                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Description</p>
                            <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                {{ $assignment->instructions ?: 'No additional instructions provided.' }}
                            </div>
                        </div>

                        @if($assignment->hasAttachment())
                            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Assignment file</p>
                                <div class="mt-3 flex flex-wrap items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 ring-1 ring-red-100">
                                            <i class="bi bi-file-earmark-pdf text-xl"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-[#0f2744]">{{ $assignmentFile['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $assignmentFile['type'] }} · {{ $assignmentFile['size'] }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.lms.assignments.attachment', [$offering, $assignment]) }}"
                                       class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3a5c] hover:shadow">
                                        <i class="bi bi-download"></i> Download assignment
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="rounded-xl border border-dashed border-gray-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Submission requirements</p>
                            <ul class="mt-3 space-y-2 text-sm text-gray-700">
                                <li class="flex items-center gap-2"><i class="bi bi-filetype-pdf text-[#8cc63f]"></i> Accepted file types: <strong>{{ $acceptedTypes }}</strong></li>
                                <li class="flex items-center gap-2"><i class="bi bi-hdd text-[#8cc63f]"></i> Maximum upload size: <strong>{{ $maxUploadMb }} MB</strong></li>
                                <li class="flex items-center gap-2"><i class="bi bi-123 text-[#8cc63f]"></i> Number of attempts: <strong>1 submission per student</strong></li>
                                <li class="flex items-center gap-2"><i class="bi bi-pencil-square text-[#8cc63f]"></i> Written response optional when uploading a file</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- 3. Your Submission --}}
                <div id="your-submission-card" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-inbox-fill text-lg text-[#0f2744]"></i>
                            <h3 class="text-base font-bold text-[#0f2744]">Your Submission</h3>
                        </div>
                        @if($canSubmit)
                            <button type="button" @click="showSubmitForm = !showSubmitForm"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#8cc63f] px-3 py-1.5 text-xs font-bold text-[#0f2744] transition hover:bg-[#7ab535]">
                                <i class="bi bi-cloud-upload"></i>
                                <span x-text="showSubmitForm ? 'Hide form' : 'Submit work'"></span>
                            </button>
                        @endif
                    </div>
                    <div class="px-6 py-5">
                        @if($submission?->submitted_at)
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                    <p class="text-xs text-gray-500">Status</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#0f2744]">{{ $statusLabel }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                    <p class="text-xs text-gray-500">Submitted</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#0f2744]">{{ $submission->submitted_at->format('d M Y, H:i') }}</p>
                                </div>
                                @if($submission->hasSubmissionFile())
                                    <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                        <p class="text-xs text-gray-500">File</p>
                                        <p class="mt-0.5 truncate text-sm font-semibold text-[#0f2744]" title="{{ $submissionFile['name'] }}">{{ $submissionFile['name'] }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                        <p class="text-xs text-gray-500">File size</p>
                                        <p class="mt-0.5 text-sm font-semibold text-[#0f2744]">{{ $submissionFile['size'] }}</p>
                                    </div>
                                @endif
                                <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                    <p class="text-xs text-gray-500">Version</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#0f2744]">1</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100">
                                    <p class="text-xs text-gray-500">Attempt</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#0f2744]">1 of 1</p>
                                </div>
                            </div>

                            @if($submission->body)
                                <div class="mt-4 rounded-xl bg-blue-50/50 p-4 ring-1 ring-blue-100">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Written response</p>
                                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-800">{{ $submission->body }}</p>
                                </div>
                            @endif

                            @if($submission->hasSubmissionFile())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('student.lms.submissions.file', [$offering, $submission]) }}"
                                       class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-[#0f2744] transition hover:bg-gray-200">
                                        <i class="bi bi-download"></i> Download submitted file
                                    </a>
                                </div>

                                @if($submission->isPdfSubmission())
                                    <div class="mt-5 overflow-hidden rounded-xl ring-1 ring-gray-200">
                                        <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2.5">
                                            <i class="bi bi-file-earmark-pdf text-red-600"></i>
                                            <span class="text-xs font-semibold text-gray-600">PDF preview</span>
                                        </div>
                                        <iframe
                                            src="{{ route('student.lms.submissions.preview', [$offering, $submission]) }}"
                                            title="Submission preview"
                                            class="h-[min(560px,70vh)] w-full bg-gray-100"
                                        ></iframe>
                                    </div>
                                @endif
                            @else
                                <p class="mt-4 text-sm text-gray-500">Text-only submission — no file attached.</p>
                            @endif

                            @if($assignment->isOpenForSubmission() && ! $submission->isGraded())
                                <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    <i class="bi bi-info-circle"></i> Submissions are still open, but only one attempt is allowed. Contact your lecturer if you need to resubmit.
                                </p>
                            @endif
                        @elseif($canSubmit)
                            <div x-show="showSubmitForm" x-cloak class="space-y-4">
                                <form method="POST" action="{{ route('student.lms.assignments.submit', [$offering, $assignment]) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Written response <span class="font-normal text-gray-400">(optional if uploading a file)</span></label>
                                        <textarea name="body" rows="5" class="mt-1.5 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]" placeholder="Type your response here…"></textarea>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Upload file</label>
                                        <input type="file" name="file" accept=".pdf,.doc,.docx" class="mt-1.5 w-full rounded-xl border-gray-300 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#0f2744] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
                                        <p class="mt-1 text-xs text-gray-500">{{ $acceptedTypes }} · max {{ $maxUploadMb }} MB</p>
                                    </div>
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8cc63f] px-5 py-2.5 text-sm font-bold text-[#0f2744] shadow-sm transition hover:bg-[#7ab535]">
                                        <i class="bi bi-send-fill"></i> Submit assignment
                                    </button>
                                </form>
                            </div>
                            <div x-show="!showSubmitForm" class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-10 text-center">
                                <i class="bi bi-cloud-upload text-4xl text-gray-300"></i>
                                <p class="mt-3 text-sm font-medium text-gray-600">You have not submitted this assignment yet.</p>
                                <button type="button" @click="showSubmitForm = true" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8cc63f] px-5 py-2.5 text-sm font-bold text-[#0f2744] transition hover:bg-[#7ab535]">
                                    <i class="bi bi-plus-lg"></i> Start submission
                                </button>
                            </div>
                        @else
                            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                                <i class="bi bi-lock-fill"></i> This assignment is closed for submission.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 5. Feedback & Results --}}
                @if($submission?->isGraded())
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                        <div class="flex items-center gap-2 border-b border-gray-100 bg-emerald-50/80 px-6 py-4">
                            <i class="bi bi-star-fill text-lg text-emerald-600"></i>
                            <h3 class="text-base font-bold text-[#0f2744]">Feedback &amp; Results</h3>
                        </div>
                        <div class="px-6 py-5">
                            <div class="flex flex-wrap items-start gap-6">
                                <div class="text-center">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Grade</p>
                                    <p class="mt-1 text-4xl font-bold text-[#0f2744]">{{ $submission->score }}<span class="text-lg text-gray-400">/{{ $assignment->max_score }}</span></p>
                                </div>
                                @if($percentage !== null)
                                    <div class="text-center">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Percentage</p>
                                        <p class="mt-1 text-3xl font-bold text-[#0f2744]">{{ $percentage }}%</p>
                                    </div>
                                @endif
                                @if($gradeBand)
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Result</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <x-grade-tone-badge :tone="$passTone" :label="$passed ? 'Pass' : 'Fail'" />
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $gradeBand['letter'] }} · {{ $gradeBand['decision'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($submission->feedback)
                                <div class="mt-6 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Lecturer comments</p>
                                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-800">{{ $submission->feedback }}</p>
                                </div>
                            @endif

                            @if($submission->hasMarkedFile())
                                <a href="{{ route('student.lms.submissions.marked', [$offering, $submission]) }}"
                                   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a3a5c]">
                                    <i class="bi bi-file-earmark-pdf"></i> Download annotated PDF
                                </a>
                            @endif

                            {{-- Marking rubric placeholder --}}
                            <div class="mt-6 rounded-xl border border-dashed border-gray-200 bg-gray-50/30 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Marking rubric</p>
                                <p class="mt-2 text-sm text-gray-500">No rubric has been attached to this assignment.</p>
                            </div>
                        </div>
                    </div>
                @elseif($submission?->submitted_at)
                    <div class="overflow-hidden rounded-2xl border border-blue-200 bg-blue-50/50 shadow-sm ring-1 ring-blue-100">
                        <div class="flex items-center gap-3 px-6 py-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                                <i class="bi bi-hourglass-split"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-blue-900">Awaiting lecturer feedback</p>
                                <p class="text-sm text-blue-800">Your submission is in the queue for grading. Results will appear here once released.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                {{-- 4. Submission Timeline --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/80 px-5 py-4">
                        <i class="bi bi-clock-history text-lg text-[#0f2744]"></i>
                        <h3 class="text-base font-bold text-[#0f2744]">Submission Timeline</h3>
                    </div>
                    <div class="px-5 py-5">
                        <div class="divide-y divide-gray-100">
                            <div class="pb-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Assignment published</p>
                                <p class="mt-0.5 text-sm font-medium text-[#0f2744]">{{ $assignment->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="py-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Student submitted</p>
                                @if($submission?->submitted_at)
                                    <p class="mt-0.5 text-sm font-medium text-[#0f2744]">{{ $submission->submitted_at->format('d M Y, H:i') }}</p>
                                    @if($isLate)
                                        <p class="mt-0.5 text-xs text-orange-600">Submitted after the due date</p>
                                    @endif
                                @else
                                    <p class="mt-0.5 text-sm text-gray-400">Pending</p>
                                @endif
                            </div>
                            <div class="py-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Lecturer graded</p>
                                @if($submission?->graded_at)
                                    <p class="mt-0.5 text-sm font-medium text-[#0f2744]">{{ $submission->graded_at->format('d M Y, H:i') }}</p>
                                @else
                                    <p class="mt-0.5 text-sm text-gray-400">Pending</p>
                                @endif
                            </div>
                            <div class="pt-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Feedback released</p>
                                @if($submission?->isGraded())
                                    <p class="mt-0.5 text-sm font-medium text-[#0f2744]">{{ $submission->graded_at->format('d M Y, H:i') }}</p>
                                @else
                                    <p class="mt-0.5 text-sm text-gray-400">Pending</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 7. Actions Panel --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/80 px-5 py-4">
                        <i class="bi bi-lightning-charge-fill text-lg text-[#8cc63f]"></i>
                        <h3 class="text-base font-bold text-[#0f2744]">Quick Actions</h3>
                    </div>
                    <div class="space-y-2 px-5 py-4">
                        @if($assignment->hasAttachment())
                            <a href="{{ route('student.lms.assignments.attachment', [$offering, $assignment]) }}"
                               class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#0f2744] transition hover:bg-gray-50">
                                <i class="bi bi-download text-gray-500"></i> Download assignment
                            </a>
                        @endif
                        @if($submission?->hasSubmissionFile())
                            <a href="{{ route('student.lms.submissions.file', [$offering, $submission]) }}"
                               class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#0f2744] transition hover:bg-gray-50">
                                <i class="bi bi-file-earmark-arrow-down text-gray-500"></i> Download submission
                            </a>
                        @endif
                        @if($submission?->hasMarkedFile())
                            <a href="{{ route('student.lms.submissions.marked', [$offering, $submission]) }}"
                               class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#0f2744] transition hover:bg-gray-50">
                                <i class="bi bi-file-earmark-check text-gray-500"></i> Download marked script
                            </a>
                        @endif
                        @if($canSubmit)
                            <button type="button" @click="$dispatch('open-submit-form')"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#8cc63f] transition hover:bg-[#8cc63f]/10">
                                <i class="bi bi-cloud-upload"></i> Submit assignment
                            </button>
                        @endif
                    </div>
                </div>

                {{-- 6. Similarity Report (Placeholder) --}}
                <div class="overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-200 bg-white/80 px-5 py-4">
                        <i class="bi bi-shield-check text-lg text-gray-400"></i>
                        <h3 class="text-base font-bold text-gray-600">Similarity Report</h3>
                        <span class="ml-auto rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-600">Coming soon</span>
                    </div>
                    <div class="space-y-4 px-5 py-5">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-white p-3 ring-1 ring-gray-100">
                                <p class="text-xs text-gray-500">Similarity</p>
                                <p class="mt-0.5 text-lg font-bold text-gray-400">—</p>
                            </div>
                            <div class="rounded-xl bg-white p-3 ring-1 ring-gray-100">
                                <p class="text-xs text-gray-500">Matching sources</p>
                                <p class="mt-0.5 text-lg font-bold text-gray-400">—</p>
                            </div>
                        </div>
                        <button type="button" disabled class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500">
                            <i class="bi bi-box-arrow-up-right"></i> View report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
