<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">My Profile</h2>
            <a href="{{ route('lecturer.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-photo-form', ['user' => auth()->user()])
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 text-sm space-y-3">
            <div class="grid sm:grid-cols-2 gap-4">
                <div><p class="text-gray-500">Name</p><p class="font-medium">{{ $staff->name }}</p></div>
                <div><p class="text-gray-500">Designation</p><p class="font-medium">{{ $staff->designation ?? 'Lecturer' }}</p></div>
                <div><p class="text-gray-500">Programme</p><p class="font-medium">{{ $staff->programme?->name ?? '—' }}</p></div>
                <div><p class="text-gray-500">Qualification</p><p class="font-medium">{{ $staff->qualification ?? '—' }}</p></div>
            </div>
        </div>
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">@include('profile.partials.update-password-form')</div>
        </div>
    </div>
</x-app-layout>
