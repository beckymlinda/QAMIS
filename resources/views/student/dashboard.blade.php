<x-app-layout>
    <div class="min-h-full bg-gradient-to-b from-slate-50 via-gray-50 to-gray-100/80">
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
            @include('partials.alerts')

            @include('student.partials.dashboard-hero')

            @include('student.partials.summary-cards')

            @include('student.partials.dashboard-evaluation-banner')

            @include('student.partials.dashboard-quick-actions')

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <div class="space-y-8 xl:col-span-2">
                    <section aria-labelledby="student-courses-heading">
                        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 id="student-courses-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">My Courses</h2>
                                <p class="mt-1 text-xs text-gray-500">{{ $summary['totalCourses'] }} {{ Str::plural('course', $summary['totalCourses']) }} this academic period</p>
                            </div>
                            <a href="{{ route('student.courses') }}" class="text-sm font-semibold text-[#0f2744] transition hover:text-[#8cc63f] focus:outline-none focus:underline">
                                Manage courses →
                            </a>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            @forelse($summary['offerings']->take(4) as $offering)
                                @include('student.partials.course-card', ['offering' => $offering, 'student' => $student])
                            @empty
                                <div class="col-span-full rounded-2xl bg-white p-12 text-center shadow-md">
                                    <i class="bi bi-journal-x text-4xl text-gray-300" aria-hidden="true"></i>
                                    <p class="mt-4 text-base font-medium text-[#0f2744]">No courses enrolled yet</p>
                                    <p class="mt-2 text-sm text-gray-500">Register for courses to access learning materials and assignments.</p>
                                    <a href="{{ route('student.courses') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1a3a5c]">
                                        Browse courses <i class="bi bi-arrow-right-short text-lg" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    @include('student.partials.dashboard-upcoming')
                </div>

                <div class="space-y-8">
                    @include('student.partials.dashboard-deadlines')
                    @include('student.partials.dashboard-notifications')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
