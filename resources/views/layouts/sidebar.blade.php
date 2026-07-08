{{-- Mobile overlay --}}
<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    x-cloak
></div>

<aside
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'w-64'
    ]"
    class="fixed inset-y-0 left-0 z-50 flex h-screen flex-col text-white shadow-xl transition-all duration-300 ease-in-out lg:shadow-none"
    style="background: var(--brand-primary, #0f2744)"
>
    <div class="flex h-16 items-center gap-3 border-b border-white/10 px-4 lg:px-5">
        <a href="{{ auth()->user()->homeRoute() }}" class="flex min-w-0 flex-1 items-center gap-2" @click="sidebarOpen = false">
            @if(!empty($institutionBranding['logo_url']))
                <img src="{{ $institutionBranding['logo_url'] }}" alt="" class="h-8 w-auto max-w-[120px] shrink-0 object-contain">
            @else
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-secondary text-sm font-bold text-brand-primary">{{ strtoupper(substr($institutionBranding['name'] ?? 'H', 0, 1)) }}</span>
            @endif
            <span x-show="!sidebarCollapsed" class="truncate text-lg font-bold tracking-wide text-white">{{ $institutionBranding['name'] ?? config('app.short_name') }}</span>
        </a>
        <button
            type="button"
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden rounded-lg p-1.5 text-blue-100 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#8cc63f] lg:inline-flex"
            aria-label="Collapse sidebar"
        >
            <i class="bi bi-layout-sidebar-inset text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-1.5 overflow-y-auto px-4 py-5">
        @role('applicant')
            <x-sidebar-link :href="route('applicant.dashboard')" :active="request()->routeIs('applicant.dashboard')" @click="sidebarOpen = false">
                <i class="bi bi-house-door-fill text-lg shrink-0 opacity-90"></i>
                <span x-show="!sidebarCollapsed">Dashboard</span>
            </x-sidebar-link>
            @php $applicantWebsite = auth()->user()->institution_id ? \App\Models\InstitutionWebsiteSetting::where('institution_id', auth()->user()->institution_id)->first() : null; @endphp
            @if($applicantWebsite)
                <x-sidebar-link :href="route('applicant.apply.create', $applicantWebsite->slug)" :active="request()->routeIs('applicant.apply.*')" @click="sidebarOpen = false">
                    <i class="bi bi-file-earmark-plus text-lg shrink-0 opacity-90"></i>
                    <span x-show="!sidebarCollapsed">Start application</span>
                </x-sidebar-link>
            @endif
            <x-sidebar-link :href="route('applicant.dashboard')" :active="request()->routeIs('applicant.applications.*')" @click="sidebarOpen = false">
                <i class="bi bi-folder2-open text-lg shrink-0 opacity-90"></i>
                <span x-show="!sidebarCollapsed">My applications</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('applicant.profile')" :active="request()->routeIs('applicant.profile')" @click="sidebarOpen = false">
                <i class="bi bi-person-circle text-lg shrink-0 opacity-90"></i>
                <span x-show="!sidebarCollapsed">Profile</span>
            </x-sidebar-link>
        @elseif(auth()->user()->hasRole('student'))
            <x-sidebar-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Portal Home
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.timetable')" :active="request()->routeIs('student.timetable')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                My Timetable
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.courses')" :active="request()->routeIs('student.courses') || request()->routeIs('student.lms.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                My Courses
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.exam-results')" :active="request()->routeIs('student.exam-results')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exam Results
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.fees')" :active="request()->routeIs('student.fees*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Tuition &amp; Fees
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.evaluations')" :active="request()->routeIs('student.evaluations*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Evaluate Lecturers
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.notifications')" :active="request()->routeIs('student.notifications*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span x-show="!sidebarCollapsed" class="flex flex-1 items-center gap-2">
                    <span>Notifications</span>
                    <x-notification-badge :count="$lmsUnreadCount ?? 0" />
                </span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('student.profile')" :active="request()->routeIs('student.profile')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Profile
            </x-sidebar-link>
        @elseif(auth()->user()->hasRole('lecturer'))
            <x-sidebar-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')" @click="sidebarOpen = false">
                <i class="bi bi-house-door-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed">Portal Home</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('lecturer.courses')" :active="request()->routeIs('lecturer.courses') || request()->routeIs('lecturer.offerings.*') || request()->routeIs('lecturer.lms.*')" @click="sidebarOpen = false">
                <i class="bi bi-journal-bookmark-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed">My Courses</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('lecturer.timetable')" :active="request()->routeIs('lecturer.timetable')" @click="sidebarOpen = false">
                <i class="bi bi-calendar-week-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed">My Timetable</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('lecturer.evaluations')" :active="request()->routeIs('lecturer.evaluations')" @click="sidebarOpen = false">
                <i class="bi bi-star-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed">Evaluations</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('lecturer.notifications')" :active="request()->routeIs('lecturer.notifications*')" @click="sidebarOpen = false">
                <i class="bi bi-bell-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed" class="flex flex-1 items-center gap-2">
                    <span>Notifications</span>
                    <x-notification-badge :count="$lmsUnreadCount ?? 0" />
                </span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('lecturer.profile')" :active="request()->routeIs('lecturer.profile')" @click="sidebarOpen = false">
                <i class="bi bi-person-circle text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed">Profile & Password</span>
            </x-sidebar-link>
        @elseif(auth()->user()->hasRole('guest_institution'))
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </x-sidebar-link>

            @if(auth()->user()->institution_id)
                @include('layouts.partials.sidebar-assessment-tools')
            @endif

            <x-sidebar-link :href="route('programmes.index')" :active="request()->routeIs('programmes.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Programmes
            </x-sidebar-link>
        @else
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
            <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </x-sidebar-link>

        @can('institution.manage')
            @if(auth()->user()->institution_id)
                <x-sidebar-link :href="route('settings.website.edit', auth()->user()->institution_id)" :active="request()->routeIs('settings.website.*')" @click="sidebarOpen = false">
                    <i class="bi bi-gear-fill text-lg shrink-0 opacity-90" aria-hidden="true"></i>
                    Settings
                </x-sidebar-link>
            @endif
        @endcan

        @can('institution.manage')
            @if(auth()->user()->institution_id)
                @include('layouts.partials.sidebar-assessment-tools')
            @endif
            <x-sidebar-link :href="auth()->user()->institution_id ? route('institutions.show', auth()->user()->institution_id) : route('institutions.index')" :active="request()->routeIs('institutions.*') && !request()->routeIs('institutions.report-data.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                {{ auth()->user()->institution_id ? 'My Institution' : 'Institutions' }}
            </x-sidebar-link>
        @endcan

        @can('programme.manage')
            <x-sidebar-link :href="route('programmes.index')" :active="request()->routeIs('programmes.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Programmes
            </x-sidebar-link>
        @endcan

        @can('viewAny', App\Models\User::class)
            <x-sidebar-dropdown label="User Management" :active="request()->routeIs('users.*') || request()->routeIs('roles.*')">
                <x-slot:icon>
                    <i class="bi bi-people-fill text-lg shrink-0 opacity-90"></i>
                </x-slot:icon>
                <x-sidebar-sublink :href="route('users.index')" :active="request()->routeIs('users.*')" @click="sidebarOpen = false">Users</x-sidebar-sublink>
                <x-sidebar-sublink :href="route('roles.index')" :active="request()->routeIs('roles.*')" @click="sidebarOpen = false">Roles & permissions</x-sidebar-sublink>
            </x-sidebar-dropdown>
        @endcan

        @can('viewAny', App\Models\Student::class)
            <x-sidebar-link :href="route('students.index')" :active="request()->routeIs('students.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Student Management
            </x-sidebar-link>
            <x-sidebar-link :href="route('fee-payments.index')" :active="request()->routeIs('fee-payments.*')" @click="sidebarOpen = false">
                <i class="bi bi-cash-stack text-lg shrink-0 opacity-90"></i>
                Fee Payments
            </x-sidebar-link>
        @endcan

        @can('application.manage')
            <x-sidebar-link :href="route('applications.index')" :active="request()->routeIs('applications.*')" @click="sidebarOpen = false">
                <i class="bi bi-file-earmark-person-fill text-lg shrink-0 opacity-90"></i>
                Applications
            </x-sidebar-link>
        @endcan

        @can('corrective_action.manage')
            <x-sidebar-link :href="route('corrective-actions.index')" :active="request()->routeIs('corrective-actions.*')" @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Actions
            </x-sidebar-link>
        @endcan

        <x-sidebar-link :href="route('search')" :active="request()->routeIs('search')" @click="sidebarOpen = false">
            <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </x-sidebar-link>
        @endrole
    </nav>

        @role('applicant')
        <div class="border-t border-white/10 px-5 py-4 text-xs leading-relaxed text-blue-200/80">
            Applicant portal — apply for admission and track your application status
        </div>
        @elseif(auth()->user()->hasRole('student'))
        <div class="border-t border-white/10 px-5 py-4 text-xs leading-relaxed text-blue-200/80">
            Student portal — LMS, timetable, courses, exam results, and teaching evaluations
        </div>
        @elseif(auth()->user()->hasRole('lecturer'))
        <div class="border-t border-white/10 px-5 py-4 text-xs leading-relaxed text-blue-200/80">
            Lecturer portal — LMS, courses, timetable, grading, and teaching evaluations
        </div>
        @elseif(auth()->user()->hasRole('guest_institution'))
        <div class="border-t border-white/10 px-5 py-4 text-xs leading-relaxed text-blue-200/80">
            Guest demo — assessment tools and programme management only
        </div>
        @endrole
</aside>
