@php
    $hour = (int) now()->format('G');
    $timeGreeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $programmeName = $staff->programme?->name ?? 'Your Programme';
    $initials = collect(explode(' ', $staff->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp

<section class="overflow-hidden rounded-2xl bg-gradient-to-br from-[#0f2744] via-[#14355a] to-[#0f2744] p-6 text-white shadow-xl sm:p-8" aria-labelledby="dashboard-hero-heading">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-[#8cc63f]">{{ now()->format('l, F j, Y') }}</p>
            <h1 id="dashboard-hero-heading" class="mt-2 text-[1.875rem] font-bold leading-tight sm:text-[2rem]">
                👋 {{ $timeGreeting }}, {{ $staff->name }}
            </h1>
            <p class="mt-2 text-lg font-medium text-white/95">{{ $programmeName }}</p>
            <p class="mt-1 text-sm text-blue-100/80">Semester {{ $semesterLabel }} · Academic Year {{ $academicYearLabel }}</p>

            <div class="mt-6 rounded-xl bg-white/10 p-4 backdrop-blur-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">You have</p>
                <ul class="mt-3 space-y-2 text-sm text-white/90">
                    <li class="flex items-center gap-2"><i class="bi bi-journal-bookmark text-[#8cc63f]" aria-hidden="true"></i> {{ $totalCourses }} assigned {{ Str::plural('course', $totalCourses) }}</li>
                    <li class="flex items-center gap-2"><i class="bi bi-calendar-event text-[#8cc63f]" aria-hidden="true"></i> {{ $upcomingClassCount }} upcoming {{ Str::plural('class', $upcomingClassCount) }}</li>
                    <li class="flex items-center gap-2"><i class="bi bi-clipboard-check text-[#8cc63f]" aria-hidden="true"></i> {{ $pendingGrades }} pending grade {{ Str::plural('submission', $pendingGrades) }}</li>
                </ul>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-3 lg:flex-col lg:items-end">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-[#8cc63f] to-[#6fa832] text-xl font-bold text-[#0f2744] shadow-lg ring-4 ring-white/20" aria-hidden="true">
                {{ $initials ?: 'LP' }}
            </div>
            <p class="hidden text-xs text-blue-100/70 lg:block">Lecturer profile</p>
        </div>
    </div>
</section>
