<header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-gray-200/80 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
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
                $initials = collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
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
                    HEQAMIS Administration
                @endrole
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        @if(auth()->user()->hasRole('lecturer'))
            @php
                $initials = $initials ?? collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
            @endphp

            <a
                href="{{ route('lecturer.evaluations') }}"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-[#0f2744] transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#8cc63f]"
                aria-label="Notifications and evaluations"
            >
                <i class="bi bi-bell text-lg" aria-hidden="true"></i>
            </a>

            <div x-data="{ open: false }" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#0f2744] to-[#1a3a5c] text-sm font-bold text-white shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#8cc63f] focus:ring-offset-2"
                    aria-label="Profile menu"
                    aria-haspopup="true"
                    x-bind:aria-expanded="open.toString()"
                >
                    {{ $initials ?: 'LP' }}
                </button>
                <div
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    x-cloak
                    class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-gray-200"
                    role="menu"
                >
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="truncate text-sm font-semibold text-[#0f2744]">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('lecturer.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none" role="menuitem">
                        <i class="bi bi-person text-gray-400" aria-hidden="true"></i>
                        Profile &amp; Password
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none" role="menuitem">
                        <i class="bi bi-gear text-gray-400" aria-hidden="true"></i>
                        Account settings
                    </a>
                </div>
            </div>
        @else
            <span class="hidden text-sm text-gray-600 sm:inline">
                Hello, <span class="font-semibold text-[#0f2744]">{{ Auth::user()->name }}</span>
            </span>
            <span class="text-sm font-semibold text-[#0f2744] sm:hidden">{{ Auth::user()->name }}</span>
            <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-[#0f2744] transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
                Profile
            </a>
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
