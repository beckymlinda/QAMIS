<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0f2744]/10 text-[#0f2744] transition-colors group-hover:bg-[#0f2744] group-hover:text-white">
            <i class="bi bi-journal-bookmark-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $summary['totalCourses'] }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Enrolled Courses</p>
        <p class="mt-1 text-xs text-gray-500">Current semester load</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-700 transition-colors group-hover:bg-amber-500 group-hover:text-white">
            <i class="bi bi-clipboard2-check-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $summary['pendingAssignments'] }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Pending Assignments</p>
        <p class="mt-1 text-xs text-gray-500">Awaiting your submission</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition-colors group-hover:bg-blue-600 group-hover:text-white">
            <i class="bi bi-bell-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">{{ $unreadNotifications }}</p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Notifications</p>
        <p class="mt-1 text-xs text-gray-500">New course updates</p>
    </div>

    <div class="group rounded-2xl bg-white p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-[#8cc63f]">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#8cc63f]/15 text-[#0f2744] transition-colors group-hover:bg-[#8cc63f]">
            <i class="bi bi-mortarboard-fill text-xl" aria-hidden="true"></i>
        </div>
        <p class="mt-5 text-[2.125rem] font-bold leading-none tracking-tight text-[#0f2744]">
            {{ $summary['semesterGpa'] !== null ? number_format($summary['semesterGpa'], 2) : '—' }}
        </p>
        <p class="mt-2 text-sm font-semibold text-[#0f2744]">Semester GPA</p>
        <p class="mt-1 text-xs text-gray-500">{{ $summary['publishedResultsCount'] }} published {{ Str::plural('result', $summary['publishedResultsCount']) }}</p>
    </div>
</div>
