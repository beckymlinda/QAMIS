<header class="z-30 flex h-16 shrink-0 items-center justify-between border-b border-gray-200/80 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
    <div class="flex min-w-0 items-center gap-3">
        <button
            type="button"
            @click="sidebarOpen = !sidebarOpen"
            class="inline-flex items-center justify-center rounded-xl p-2 text-[#0f2744] transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#8cc63f] lg:hidden"
            aria-label="Toggle sidebar"
        >
            <svg x-show="!sidebarOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="sidebarOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        @if(auth()->user()->hasRole('lecturer'))
            @php
                $lecturerStaff = auth()->user()->staffMember?->loadMissing('programme');
                $hour = (int) now()->format('G');
                $timeGreeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                $departmentLabel = $lecturerStaff?->programme?->name
                    ?: ($lecturerStaff?->designation ?: 'Lecturer Portal');
            @endphp
            <div class="min-w-0 hidden sm:block">
                <p class="truncate text-base font-bold text-[#0f2744]">{{ $timeGreeting }}, {{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ $departmentLabel }}</p>
            </div>
            <span class="text-sm font-semibold text-[#0f2744] sm:hidden">Lecturer Portal</span>
        @else
            <span class="hidden text-sm font-medium text-[#0f2744] sm:inline">
                @role('student')
                    Student Portal
                @else
                    {{ config('app.short_name') }} Administration
                @endrole
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        @if(auth()->user()->hasRole('lecturer'))
            <x-notification-bell :href="route('lecturer.notifications')" />
            <x-topbar-profile-menu />
        @elseif(auth()->user()->hasRole('student'))
            <x-notification-bell :href="route('student.notifications')" />
            <x-topbar-profile-menu />
        @else
            <x-topbar-profile-menu />
        @endif

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button
                type="submit"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-[#0f2744] shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#8cc63f] focus:ring-offset-2 sm:px-4"
            >
                Log out
            </button>
        </form>
    </div>
</header>
