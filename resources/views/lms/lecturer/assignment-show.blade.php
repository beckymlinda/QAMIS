<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Assignment</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $assignment->title }}</h2>
            </div>
            <a href="{{ route('lecturer.lms.assignments', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to assignments</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'lecturer'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-5">
                <h3 class="text-lg font-bold text-[#0f2744]">{{ $assignment->title }}</h3>
                <div class="mt-3 flex flex-wrap gap-3 text-xs">
                    <span class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800">Due date: {{ $assignment->due_at?->format('d M Y H:i') ?? 'No deadline' }}</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-800">Max grade: {{ $assignment->max_score }}</span>
                    <span class="rounded-full px-3 py-1 font-medium {{ $assignment->is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $assignment->is_published ? 'Published' : 'Draft' }}</span>
                    @if($assignment->allow_late)
                        <span class="rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700">Late submissions allowed</span>
                    @endif
                </div>
            </div>

            <div class="space-y-6 px-6 py-5">
                @if($assignment->instructions)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Instructions</p>
                        <p class="mt-2 whitespace-pre-wrap text-sm text-gray-700">{{ $assignment->instructions }}</p>
                    </div>
                @endif

                @if($assignment->hasAttachment())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Attachment</p>
                        <a href="{{ route('lecturer.lms.assignments.attachment', [$offering, $assignment]) }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-[#0f2744] hover:text-[#8cc63f]">
                            <i class="bi bi-file-earmark-pdf"></i> Download assignment PDF
                        </a>
                    </div>
                @endif

                <div class="border-t border-gray-100 pt-5">
                    <a href="{{ route('lecturer.lms.assignments.submissions', [$offering, $assignment]) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-5 py-3 text-sm font-semibold text-white hover:bg-[#1a3a5c]">
                        <i class="bi bi-inbox"></i> View submissions &amp; grade ({{ $assignment->submissions_count }})
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
