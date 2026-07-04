<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Grade submission</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $submission->student->fullName() }}</h2>
                <p class="text-sm text-gray-500">{{ $assignment->title }}</p>
            </div>
            <a href="{{ route('lecturer.lms.assignments.submissions', [$offering, $assignment]) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to submissions</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3 space-y-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <h3 class="font-semibold text-[#0f2744]">Submitted work</h3>
                    <p class="mt-1 text-xs text-gray-500">Submitted {{ $submission->submitted_at?->format('d M Y H:i') }}</p>

                    @if($submission->body)
                        <div class="mt-4 rounded-xl bg-gray-50 p-4 text-sm whitespace-pre-wrap text-gray-800">{{ $submission->body }}</div>
                    @endif

                    @if($submission->hasSubmissionFile())
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('lecturer.lms.submissions.file', [$offering, $submission]) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-[#0f2744] hover:bg-gray-200"><i class="bi bi-download"></i> Download original</a>
                        </div>

                        @if($submission->isPdfSubmission())
                            <div
                                class="mt-4 overflow-hidden rounded-xl ring-1 ring-gray-200"
                                data-lms-pdf-grader
                                data-pdf-url="{{ route('lecturer.lms.submissions.preview', [$offering, $submission]) }}"
                                data-existing-annotations='@json($submission->annotation_data ?? ["pages" => []])'
                            >
                                <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 bg-gray-50 px-3 py-2">
                                    <button type="button" data-tool-pen class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 ring-gray-200 transition hover:bg-gray-100">
                                        <i class="bi bi-pen"></i> Pen
                                    </button>
                                    <span class="mx-1 h-5 w-px bg-gray-200"></span>
                                    <button type="button" data-zoom-out class="rounded-lg p-1.5 text-gray-700 hover:bg-gray-200" title="Zoom out"><i class="bi bi-zoom-out"></i></button>
                                    <button type="button" data-zoom-reset class="rounded-lg px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200" title="Reset zoom">125%</button>
                                    <button type="button" data-zoom-in class="rounded-lg p-1.5 text-gray-700 hover:bg-gray-200" title="Zoom in"><i class="bi bi-zoom-in"></i></button>
                                    <span class="mx-1 h-5 w-px bg-gray-200"></span>
                                    <button type="button" data-undo class="rounded-lg px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"><i class="bi bi-arrow-counterclockwise"></i> Undo</button>
                                    <button type="button" data-clear-page class="rounded-lg px-2 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Clear page</button>
                                    <span class="ml-auto flex items-center gap-2">
                                        <button type="button" data-page-prev class="rounded-lg p-1.5 hover:bg-gray-200 disabled:opacity-40"><i class="bi bi-chevron-left"></i></button>
                                        <span data-page-label class="text-xs font-medium text-gray-600">Page 1</span>
                                        <button type="button" data-page-next class="rounded-lg p-1.5 hover:bg-gray-200 disabled:opacity-40"><i class="bi bi-chevron-right"></i></button>
                                    </span>
                                </div>
                                <div data-pdf-viewport class="max-h-[560px] overflow-auto bg-gray-200 p-4 text-center"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Use the <strong>Pen</strong> tool to scribble on the PDF. Zoom and page controls are in the toolbar. Marks are saved into the PDF when you click <strong>Save grade</strong>.</p>
                        @endif
                    @else
                        <p class="mt-4 text-sm text-gray-500">No file attached — text response only.</p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('lecturer.lms.submissions.grade', [$offering, $submission]) }}" enctype="multipart/form-data" data-grade-form class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 space-y-4">
                    @csrf
                    <input type="hidden" name="annotation_data" data-annotation-input value="">
                    <input type="file" name="marked_file" data-marked-file-input class="hidden" accept="application/pdf">

                    <h3 class="font-semibold text-[#0f2744]">Grade &amp; feedback</h3>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Grade (out of {{ $assignment->max_score }})</label>
                        <input type="number" step="0.01" name="score" value="{{ old('score', $submission->score) }}" max="{{ $assignment->max_score }}" required class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Feedback</label>
                        <textarea name="feedback" rows="4" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">{{ old('feedback', $submission->feedback) }}</textarea>
                    </div>

                    @if($submission->isPdfSubmission())
                        <div>
                            <label class="text-sm font-medium text-gray-700">Or upload marked PDF manually</label>
                            <input type="file" id="manual-marked-file" accept="application/pdf" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                            <p class="mt-1 text-xs text-gray-500">Optional — overrides auto-generated marked PDF.</p>
                        </div>
                    @endif

                    @if($submission->hasMarkedFile())
                        <a href="{{ route('lecturer.lms.submissions.marked', [$offering, $submission]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0f2744] hover:text-[#8cc63f]"><i class="bi bi-file-earmark-pdf"></i> Download current marked PDF</a>
                    @endif

                    <button type="submit" class="w-full rounded-xl bg-[#8cc63f] px-4 py-3 text-sm font-bold text-[#0f2744] shadow-sm hover:bg-[#7ab535]">Save grade</button>
                </form>
            </div>
        </div>
    </div>

    @if($submission->isPdfSubmission())
        <x-slot name="scripts">
            @vite(['resources/js/lms-pdf-grader.js'])
        </x-slot>
    @endif
</x-app-layout>
