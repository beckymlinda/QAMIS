<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">{{ $discussion->title }}</h2>
            <a href="{{ route('lecturer.lms.discussions', $offering) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Discussions</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">{{ $discussion->author?->name }} · {{ $discussion->created_at->format('d M Y H:i') }}</p>
            <p class="mt-3 whitespace-pre-wrap">{{ $discussion->body }}</p>
        </div>
        @foreach($discussion->posts as $post)
            <div class="bg-white rounded-lg shadow p-4 ml-0 {{ $post->parent_id ? 'md:ml-8' : '' }}">
                <p class="text-sm text-gray-500">{{ $post->user?->name }} · {{ $post->created_at->format('d M Y H:i') }}</p>
                <p class="mt-2 whitespace-pre-wrap">{{ $post->body }}</p>
            </div>
        @endforeach
        <form method="POST" action="{{ route('lecturer.lms.discussions.posts.store', [$offering, $discussion]) }}" class="bg-white rounded-lg shadow p-5 space-y-3">
            @csrf
            <textarea name="body" rows="3" required class="w-full rounded-lg border-gray-300" placeholder="Write a reply"></textarea>
            <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 font-semibold text-[#0f2744]">Post reply</button>
        </form>
    </div>
</x-app-layout>
