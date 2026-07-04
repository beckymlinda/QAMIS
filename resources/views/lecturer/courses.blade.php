<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Lecturer Portal</p>
                <h1 class="mt-1 text-[1.875rem] font-bold text-[#0f2744]">My Courses</h1>
                <p class="mt-2 text-sm text-gray-500">All courses assigned to you this academic period</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-[#0f2744] transition hover:text-[#8cc63f] focus:outline-none focus:underline sm:mt-0">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to dashboard
            </a>
        </div>
    </x-slot>

    <div class="min-h-full bg-gradient-to-b from-slate-50 via-gray-50 to-gray-100/80">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @include('partials.alerts')

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                @forelse($offerings as $offering)
                    @include('lecturer.partials.course-card', ['offering' => $offering])
                @empty
                    <div class="col-span-full rounded-2xl bg-white p-12 text-center shadow-md">
                        <i class="bi bi-journal-x text-4xl text-gray-300" aria-hidden="true"></i>
                        <p class="mt-4 text-base font-medium text-[#0f2744]">No courses assigned yet.</p>
                        <p class="mt-2 text-sm text-gray-500">Contact your institution administrator.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
