<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Overview</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'overview', 'role' => 'student'])

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100"><p class="text-sm text-gray-500">Pending assignments</p><p class="text-2xl font-bold text-[#0f2744]">{{ $progress['pendingCount'] }}</p></div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100"><p class="text-sm text-gray-500">Published modules</p><p class="text-2xl font-bold text-[#0f2744]">{{ $modules->count() }}</p></div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100"><p class="text-sm text-gray-500">Lecturer</p><p class="text-lg font-semibold text-[#0f2744]">{{ $offering->lecturer?->name ?? 'TBA' }}</p></div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h4 class="mb-3 font-semibold text-[#0f2744]">Learning outcomes</h4>
                <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $outline->learning_outcomes ?: 'Not provided yet.' }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h4 class="mb-3 font-semibold text-[#0f2744]">Upcoming deadlines</h4>
                <ul class="space-y-2 text-sm">
                    @forelse($progress['upcomingDeadlines'] as $assignment)
                        <li><a href="{{ route('student.lms.assignments.show', [$offering, $assignment]) }}" class="text-[#0f2744] underline">{{ $assignment->title }}</a> — {{ $assignment->due_at->format('d M Y H:i') }}</li>
                    @empty
                        <li class="text-gray-500">No upcoming deadlines.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 md:col-span-2">
                <h4 class="mb-3 font-semibold text-[#0f2744]">Announcements</h4>
                @forelse($announcements as $item)
                    <div class="border-b py-3 last:border-0">
                        <p class="font-medium text-[#0f2744]">{{ $item->title }}</p>
                        <p class="text-xs text-gray-500">{{ $item->published_at->format('d M Y') }}</p>
                        <p class="mt-2 whitespace-pre-wrap text-sm">{{ $item->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No announcements.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
