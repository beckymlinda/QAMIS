<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">My Profile</h2>
            <a href="{{ route('student.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4 text-sm">
            <div class="grid sm:grid-cols-2 gap-4">
                <div><p class="text-gray-500">Full name</p><p class="font-medium text-[#0f2744]">{{ $student->fullName() }}</p></div>
                <div><p class="text-gray-500">Student number</p><p class="font-medium">{{ $student->student_number }}</p></div>
                <div><p class="text-gray-500">Email</p><p class="font-medium">{{ $student->email }}</p></div>
                <div><p class="text-gray-500">Institution</p><p class="font-medium">{{ $student->institution->name }}</p></div>
                <div><p class="text-gray-500">Programme</p><p class="font-medium">{{ $student->programme->name }}</p></div>
                <div><p class="text-gray-500">Year of study</p><p class="font-medium">Year {{ $student->year_of_study }}</p></div>
                <div><p class="text-gray-500">Status</p><p class="font-medium capitalize">{{ $student->status }}</p></div>
            </div>
            <p class="text-xs text-gray-500 pt-4 border-t">Contact your institution administrator to update registration details.</p>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
