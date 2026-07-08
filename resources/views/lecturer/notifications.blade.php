<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Lecturer Portal</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Notifications</h2>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Dashboard</a>
        </div>
    </x-slot>

    @include('lms.partials.notifications-list', ['readRoute' => 'lecturer.notifications.read'])
</x-app-layout>
