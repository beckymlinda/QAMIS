<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Course LMS</h2>
            <a href="{{ route('lecturer.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'overview', 'role' => 'lecturer'])

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Enrolled students</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['enrolledCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Published modules</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['moduleCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Assignment completion</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['completionRate'] }}%</p></div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="font-semibold text-[#0f2744] mb-3">Course outline</h4>
                @if($outline->learning_outcomes)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ Str::limit($outline->learning_outcomes, 300) }}</p>
                @else
                    <p class="text-sm text-gray-500">No learning outcomes added yet. <a href="{{ route('lecturer.lms.outline', $offering) }}" class="text-[#0f2744] underline">Add outline</a></p>
                @endif
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="font-semibold text-[#0f2744] mb-3">Recent announcements</h4>
                <ul class="text-sm space-y-2">
                    @forelse($announcements as $item)
                        <li class="border-b pb-2"><strong>{{ $item->title }}</strong><br><span class="text-gray-500">{{ $item->published_at?->format('d M Y') ?? 'Draft' }}</span></li>
                    @empty
                        <li class="text-gray-500">No announcements yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
