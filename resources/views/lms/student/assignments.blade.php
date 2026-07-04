<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Assignments</h2>
            <a href="{{ route('student.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'student'])
        @foreach($progress['assignments'] as $assignment)
            @php $submission = $progress['submissions']->get($assignment->id); @endphp
            <div class="bg-white rounded-lg shadow p-5 flex flex-wrap justify-between gap-3">
                <div>
                    <h4 class="font-semibold text-[#0f2744]">{{ $assignment->title }}</h4>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-medium text-amber-700">Due date:</span> {{ $assignment->due_at?->format('d M Y H:i') ?? 'No deadline' }} ·
                        <span class="font-medium text-blue-700">Max grade:</span> {{ $assignment->max_score }} ·
                        @if($submission?->submitted_at)
                            Submitted {{ $submission->submitted_at->format('d M Y') }}
                            @if($submission->isGraded())
                                · Grade {{ $submission->score }}/{{ $assignment->max_score }}
                            @endif
                        @else
                            Not submitted
                        @endif
                    </p>
                </div>
                <a href="{{ route('student.lms.assignments.show', [$offering, $assignment]) }}" class="rounded-lg bg-[#0f2744] px-4 py-2 text-sm text-white">Open</a>
            </div>
        @endforeach
    </div>
</x-app-layout>
