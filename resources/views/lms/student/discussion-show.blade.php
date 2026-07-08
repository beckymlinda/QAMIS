<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Discussions</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Discussion forum</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'discussions', 'role' => 'student'])

        @include('lms.partials.discussion-chat', [
            'offering' => $offering,
            'discussion' => $discussion,
            'messages' => $messages,
            'isCreator' => $isCreator,
            'role' => 'student',
        ])
    </div>
</x-app-layout>
