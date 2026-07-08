<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-brand-primary">My profile</h2>
    </x-slot>
    <div class="mx-auto max-w-lg px-4 py-8">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-3 text-sm">
            <div><p class="text-gray-500">Name</p><p class="font-medium">{{ $user->name }}</p></div>
            <div><p class="text-gray-500">Email</p><p class="font-medium">{{ $user->email }}</p></div>
            <div><p class="text-gray-500">Account type</p><p class="font-medium">Applicant</p></div>
        </div>
    </div>
</x-app-layout>
