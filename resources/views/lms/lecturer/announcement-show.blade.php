<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Announcement</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $announcement->title }}</h2>
            </div>
            <a href="{{ route('lecturer.lms.announcements', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to announcements</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'announcements', 'role' => 'lecturer'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-5">
                <h3 class="text-lg font-bold text-[#0f2744]">{{ $announcement->title }}</h3>
                <p class="mt-2 text-xs text-gray-500">
                    {{ $announcement->isPublished() ? 'Published' : 'Draft' }}
                    · {{ $announcement->published_at?->format('d M Y H:i') ?? $announcement->created_at->format('d M Y H:i') }}
                    · {{ $announcement->author?->name }}
                </p>
            </div>
            <div class="px-6 py-5">
                <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $announcement->body }}</p>

                @unless($announcement->isPublished())
                    <form method="POST" action="{{ route('lecturer.lms.announcements.publish', [$offering, $announcement]) }}" class="mt-6">
                        @csrf
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Publish and notify students</button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
