<section aria-labelledby="quick-actions-heading">
    <div class="mb-5">
        <h2 id="quick-actions-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Quick Actions</h2>
        <p class="mt-1 text-xs text-gray-500">Jump to common teaching tasks</p>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('lecturer.courses') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0f2744]/10 text-[#0f2744] transition group-hover:bg-[#0f2744] group-hover:text-white"><i class="bi bi-journals text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">My Courses</span>
        </a>
        <a href="{{ route('lecturer.timetable') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition group-hover:bg-blue-600 group-hover:text-white"><i class="bi bi-calendar-week text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">Timetable</span>
        </a>
        <a href="{{ route('lecturer.courses') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-700 transition group-hover:bg-amber-500 group-hover:text-white"><i class="bi bi-pencil-square text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">Gradebook</span>
        </a>
        <a href="{{ route('lecturer.evaluations') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white"><i class="bi bi-bar-chart-line text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">Reports</span>
        </a>
        <a href="{{ route('lecturer.evaluations') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-700 transition group-hover:bg-violet-600 group-hover:text-white"><i class="bi bi-star-fill text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">Evaluations</span>
        </a>
        <a href="{{ route('lecturer.profile') }}" class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-700 transition group-hover:bg-gray-700 group-hover:text-white"><i class="bi bi-gear-fill text-xl" aria-hidden="true"></i></span>
            <span class="mt-3 text-sm font-semibold text-[#0f2744]">Profile</span>
        </a>
    </div>
</section>
