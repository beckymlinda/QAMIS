@props(['offering'])

@php
    $studentCount = $offering->studentEnrolments->count();
    $assessmentCount = $offering->relationLoaded('lmsAssignments')
        ? $offering->lmsAssignments->count()
        : $offering->lmsAssignments()->count();

    $gradedResults = $offering->studentEnrolments
        ->map(fn ($e) => $e->result?->final_percentage)
        ->filter(fn ($v) => $v !== null);

    $averageGrade = $gradedResults->isNotEmpty()
        ? round($gradedResults->avg(), 1)
        : null;

    $gradedCount = $gradedResults->count();
    $assessmentProgress = $studentCount > 0
        ? (int) round(($gradedCount / $studentCount) * 100)
        : 0;
@endphp

<article class="group flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
    <div class="flex items-start gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0f2744] to-[#1a3a5c] text-white shadow-md" aria-hidden="true">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-xl font-bold leading-snug text-[#0f2744] sm:text-2xl">{{ $offering->course->title }}</h3>
            <p class="mt-1 text-sm font-semibold text-[#8cc63f]">{{ $offering->course->code }}</p>
            <p class="mt-2 text-sm text-gray-500">Semester {{ $offering->semester }} · {{ $offering->academic_year }}</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#0f2744] ring-1 ring-gray-100">
            <svg class="h-4 w-4 text-[#8cc63f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $studentCount }} {{ Str::plural('Student', $studentCount) }}
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#0f2744] ring-1 ring-gray-100">
            <svg class="h-4 w-4 text-[#8cc63f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ $assessmentCount }} {{ Str::plural('Assessment', $assessmentCount) }}
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#0f2744] ring-1 ring-gray-100">
            <svg class="h-4 w-4 text-[#8cc63f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @if($averageGrade !== null)
                Avg {{ $averageGrade }}%
            @else
                No grades yet
            @endif
        </span>
    </div>

    <div class="mt-6">
        <div class="mb-2 flex items-center justify-between text-xs">
            <span class="font-medium text-[#0f2744]">Assessment Progress</span>
            <span class="font-semibold text-[#8cc63f]">{{ $assessmentProgress }}%</span>
        </div>
        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $assessmentProgress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Assessment progress for {{ $offering->course->code }}">
            <div
                class="lecturer-progress-fill h-full rounded-full bg-gradient-to-r from-[#8cc63f] to-[#6fa832] transition-all duration-1000 ease-out"
                style="width: {{ $assessmentProgress }}%"
            ></div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <a
            href="{{ route('lecturer.offerings.students', $offering) }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0f2744] px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#1a3a5c] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#8cc63f] focus:ring-offset-2"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Students
        </a>
        <a
            href="{{ route('lecturer.lms.show', $offering) }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#0f2744] shadow-sm ring-1 ring-gray-200 transition-all duration-200 hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#8cc63f] focus:ring-offset-2"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Open LMS
        </a>
        <a
            href="{{ route('lecturer.lms.grades', $offering) }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8cc63f] px-4 py-3 text-sm font-semibold text-[#0f2744] shadow-sm transition-all duration-200 hover:bg-[#7ab535] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0f2744] focus:ring-offset-2"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Grades
        </a>
    </div>
</article>
