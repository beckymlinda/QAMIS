@php
    $hour = (int) now()->format('G');
    $timeGreeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $initials = collect(explode(' ', $student->fullName()))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp

<section class="overflow-hidden rounded-2xl bg-gradient-to-br from-[#0f2744] via-[#14355a] to-[#0f2744] p-6 text-white shadow-xl sm:p-8" aria-labelledby="student-dashboard-heading">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-[#8cc63f]">{{ now()->format('l, F j, Y') }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-blue-100/70">Student Portal</p>
            <h1 id="student-dashboard-heading" class="mt-2 text-[1.875rem] font-bold leading-tight sm:text-[2rem]">
                {{ $timeGreeting }}, {{ $student->first_name }}
            </h1>
            <p class="mt-2 text-lg font-medium text-white/95">{{ $student->programme->name }}</p>
            <p class="mt-1 text-sm text-blue-100/80">
                {{ $student->student_number }} · Year {{ $student->year_of_study }} · Semester {{ $summary['semesterLabel'] }} · {{ $summary['academicYearLabel'] }}
            </p>

            <div class="mt-6 rounded-xl bg-white/10 p-4 backdrop-blur-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Your snapshot</p>
                <ul class="mt-3 space-y-2 text-sm text-white/90">
                    <li class="flex items-center gap-2">
                        <i class="bi bi-journal-bookmark text-[#8cc63f]" aria-hidden="true"></i>
                        {{ $summary['totalCourses'] }} enrolled {{ Str::plural('course', $summary['totalCourses']) }}
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="bi bi-calendar-event text-[#8cc63f]" aria-hidden="true"></i>
                        {{ $upcomingSlots->count() }} upcoming {{ Str::plural('class', $upcomingSlots->count()) }}
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="bi bi-clipboard2-check text-[#8cc63f]" aria-hidden="true"></i>
                        {{ $summary['pendingAssignments'] }} pending assignment {{ Str::plural('submission', $summary['pendingAssignments']) }}
                    </li>
                    @if($unreadNotifications > 0)
                        <li class="flex items-center gap-2">
                            <i class="bi bi-bell-fill text-[#8cc63f]" aria-hidden="true"></i>
                            {{ $unreadNotifications }} unread {{ Str::plural('notification', $unreadNotifications) }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-3 lg:flex-col lg:items-end">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-[#8cc63f] to-[#6fa832] text-xl font-bold text-[#0f2744] shadow-lg ring-4 ring-white/20" aria-hidden="true">
                {{ $initials ?: 'SP' }}
            </div>
            <p class="hidden text-xs text-blue-100/70 lg:block">Student profile</p>
        </div>
    </div>
</section>
