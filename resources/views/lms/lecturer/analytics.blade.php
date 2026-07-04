<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Learning analytics</h2>
            <a href="{{ route('lecturer.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'analytics', 'role' => 'lecturer'])
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Enrolled students</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['enrolledCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Learning materials</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['materialCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Assignment submissions</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['submissionCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Graded submissions</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['gradedCount'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Discussion posts</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['discussionPosts'] }}</p></div>
            <div class="bg-white rounded-lg shadow p-5"><p class="text-sm text-gray-500">Completion rate</p><p class="text-2xl font-bold text-[#0f2744]">{{ $analytics['completionRate'] }}%</p></div>
        </div>
    </div>
</x-app-layout>
