@props([
    'totalCourses',
    'totalStudents',
    'pendingGrades',
    'teachingEvaluationsCount',
])

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0f2744]/10 text-[#0f2744] transition-colors group-hover:bg-[#0f2744] group-hover:text-white">
            <i class="bi bi-journal-bookmark-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $totalCourses }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Assigned Courses</p>
        <p class="mt-1 text-xs text-gray-500">Current teaching load</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#8cc63f]/15 text-[#0f2744] transition-colors group-hover:bg-[#8cc63f]">
            <i class="bi bi-people-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $totalStudents }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Enrolled Students</p>
        <p class="mt-1 text-xs text-gray-500">Across all assigned courses</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-700 transition-colors group-hover:bg-amber-500 group-hover:text-white">
            <i class="bi bi-clipboard-check-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $pendingGrades }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Pending Grades</p>
        <p class="mt-1 text-xs text-gray-500">Awaiting grade entry</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition-colors group-hover:bg-blue-600 group-hover:text-white">
            <i class="bi bi-star-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $teachingEvaluationsCount }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Teaching Evaluations</p>
        <p class="mt-1 text-xs text-gray-500">Student feedback received</p>
    </div>
</div>
