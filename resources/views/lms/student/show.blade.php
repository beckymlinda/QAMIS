<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Course workspace</h2>
            <a href="{{ route('student.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'overview', 'role' => 'student'])

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Pending assignments</p><p class="text-2xl font-bold text-[#0f2744]">{{ $progress['pendingCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Published modules</p><p class="text-2xl font-bold text-[#0f2744]">{{ $modules->count() }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Lecturer</p><p class="text-lg font-semibold text-[#0f2744]">{{ $offering->lecturer?->name ?? 'TBA' }}</p></div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="font-semibold text-[#0f2744] mb-3">Learning outcomes</h4>
                <p class="text-sm whitespace-pre-wrap text-gray-700">{{ $outline->learning_outcomes ?: 'Not provided yet.' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="font-semibold text-[#0f2744] mb-3">Upcoming deadlines</h4>
                <ul class="text-sm space-y-2">
                    @forelse($progress['upcomingDeadlines'] as $assignment)
                        <li><a href="{{ route('student.lms.assignments.show', [$offering, $assignment]) }}" class="text-[#0f2744] underline">{{ $assignment->title }}</a> — {{ $assignment->due_at->format('d M Y H:i') }}</li>
                    @empty
                        <li class="text-gray-500">No upcoming deadlines.</li>
                    @endforelse
                </ul>
            </div>
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h4 class="font-semibold text-[#0f2744] mb-3">Announcements</h4>
                @forelse($announcements as $item)
                    <div class="border-b py-3 last:border-0">
                        <p class="font-medium text-[#0f2744]">{{ $item->title }}</p>
                        <p class="text-xs text-gray-500">{{ $item->published_at->format('d M Y') }}</p>
                        <p class="text-sm mt-2 whitespace-pre-wrap">{{ $item->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No announcements.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
