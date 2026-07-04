<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Discussions</h2>
            <a href="{{ route('student.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'discussions', 'role' => 'student'])

        <form method="POST" action="{{ route('student.lms.discussions.store', $offering) }}" class="bg-white rounded-lg shadow p-5 space-y-3">
            @csrf
            <h4 class="font-semibold text-[#0f2744]">Start a topic</h4>
            <input type="text" name="title" required class="w-full rounded-lg border-gray-300">
            <textarea name="body" rows="3" required class="w-full rounded-lg border-gray-300"></textarea>
            <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-sm text-white">Post topic</button>
        </form>

        @foreach($discussions->sortByDesc('is_pinned') as $discussion)
            <a href="{{ route('student.lms.discussions.show', [$offering, $discussion]) }}" class="block bg-white rounded-lg shadow p-5">
                <h4 class="font-semibold text-[#0f2744]">
                    @if($discussion->is_pinned)📌 @endif
                    {{ $discussion->title }}
                </h4>
                <p class="text-sm text-gray-500 mt-1">{{ $discussion->posts->count() }} replies</p>
            </a>
        @endforeach
    </div>
</x-app-layout>
