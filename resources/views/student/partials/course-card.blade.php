@props(['offering', 'student'])

@php
    $progress = app(\App\Services\LmsService::class)->studentProgress($student, $offering);
    $pendingCount = $progress['pendingCount'];
    $totalAssignments = $progress['assignments']->count();
    $submittedCount = $totalAssignments - $pendingCount;
    $completionPercent = $totalAssignments > 0 ? (int) round(($submittedCount / $totalAssignments) * 100) : 0;
@endphp

<article class="group flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
    <div class="flex items-start gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0f2744] to-[#1a3a5c] text-white shadow-md" aria-hidden="true">
            <i class="bi bi-book text-2xl"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-bold leading-snug text-[#0f2744]">{{ $offering->course->title }}</h3>
            <p class="mt-1 text-sm font-semibold text-[#8cc63f]">{{ $offering->course->code }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ $offering->lecturer?->name ?? 'Lecturer TBA' }}</p>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#0f2744] ring-1 ring-gray-100">
            <i class="bi bi-calendar3 text-[#8cc63f]" aria-hidden="true"></i>
            Sem {{ $offering->semester }} · {{ $offering->academic_year }}
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#0f2744] ring-1 ring-gray-100">
            <i class="bi bi-clipboard2-check text-[#8cc63f]" aria-hidden="true"></i>
            {{ $pendingCount }} pending
        </span>
    </div>

    @if($totalAssignments > 0)
        <div class="mt-5">
            <div class="mb-2 flex items-center justify-between text-xs">
                <span class="font-medium text-[#0f2744]">Assignment progress</span>
                <span class="font-semibold text-[#8cc63f]">{{ $completionPercent }}%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-gradient-to-r from-[#8cc63f] to-[#6fa832]" style="width: {{ $completionPercent }}%"></div>
            </div>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <a href="{{ route('student.lms.show', $offering) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0f2744] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3a5c] focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
            Open LMS
        </a>
        <a href="{{ route('student.lms.assignments', $offering) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#0f2744] shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
            Assignments
        </a>
    </div>
</article>
