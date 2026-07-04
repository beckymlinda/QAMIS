<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Announcements</h2>
            <a href="{{ route('lecturer.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'announcements', 'role' => 'lecturer'])

        <form method="POST" action="{{ route('lecturer.lms.announcements.store', $offering) }}" class="bg-white rounded-lg shadow p-5 space-y-3">
            @csrf
            <h4 class="font-semibold text-[#0f2744]">Post announcement</h4>
            <input type="text" name="title" required placeholder="Title" class="w-full rounded-lg border-gray-300">
            <textarea name="body" rows="4" required placeholder="Message to students" class="w-full rounded-lg border-gray-300"></textarea>
            <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="publish_now" value="1" checked> Publish and notify students</label>
            <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white">Save announcement</button>
        </form>

        @foreach($announcements as $announcement)
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-[#0f2744]">{{ $announcement->title }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $announcement->published_at?->format('d M Y H:i') ?? 'Draft' }} · {{ $announcement->author?->name }}</p>
                    </div>
                    <div class="flex gap-2 text-sm">
                        @unless($announcement->isPublished())
                            <form method="POST" action="{{ route('lecturer.lms.announcements.publish', [$offering, $announcement]) }}">@csrf<button class="text-[#0f2744] underline">Publish</button></form>
                        @endunless
                        <form method="POST" action="{{ route('lecturer.lms.announcements.destroy', [$offering, $announcement]) }}" onsubmit="return confirm('Delete announcement?');">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
                    </div>
                </div>
                <p class="text-sm text-gray-700 mt-3 whitespace-pre-wrap">{{ $announcement->body }}</p>
            </div>
        @endforeach
    </div>
</x-app-layout>
