<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Submissions</h2>
                <p class="text-sm text-gray-500">{{ $assignment->title }}</p>
            </div>
            <a href="{{ route('lecturer.lms.assignments', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Assignments</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'lecturer'])

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap gap-3 text-xs">
                <span class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800">Due date: {{ $assignment->due_at?->format('d M Y H:i') ?? 'No deadline' }}</span>
                <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-800">Max grade: {{ $assignment->max_score }}</span>
            </div>
        </div>

        @forelse($assignment->submissions as $submission)
            <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="font-semibold text-[#0f2744]">{{ $submission->student->fullName() }}</h4>
                    <p class="mt-1 text-xs text-gray-500">Submitted {{ $submission->submitted_at?->format('d M Y H:i') ?? 'Not submitted' }}</p>
                    @if($submission->isGraded())
                        <p class="mt-2 text-sm font-medium text-green-700">Grade: {{ $submission->score }}/{{ $assignment->max_score }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($submission->submitted_at)
                        <a href="{{ route('lecturer.lms.submissions.show', [$offering, $submission]) }}" class="rounded-lg bg-[#0f2744] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1a3a5c]">View &amp; grade</a>
                    @else
                        <span class="text-xs text-gray-400">No submission</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
                <p class="text-sm text-gray-500">No submissions yet.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
