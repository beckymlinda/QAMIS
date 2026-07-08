<section aria-labelledby="student-deadlines-heading">
    <div class="mb-5">
        <h2 id="student-deadlines-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Assignment Deadlines</h2>
        <p class="mt-1 text-xs text-gray-500">Due soon across your courses</p>
    </div>

    <div class="space-y-3">
        @forelse($summary['upcomingDeadlines'] as $item)
            @php
                $assignment = $item['assignment'];
                $offering = $item['offering'];
            @endphp
            <a href="{{ route('student.lms.assignments.show', [$offering, $assignment]) }}" class="block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#8cc63f]/30">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#8cc63f]">{{ $offering->course->code }}</p>
                        <p class="mt-1 font-semibold text-[#0f2744]">{{ $assignment->title }}</p>
                        <p class="mt-1 text-xs text-gray-500">Due {{ $assignment->due_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-800">
                        {{ $assignment->due_at->diffForHumans(null, true) }} left
                    </span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                <i class="bi bi-check2-circle text-3xl text-[#8cc63f]" aria-hidden="true"></i>
                <p class="mt-3 text-sm text-gray-500">No upcoming assignment deadlines.</p>
            </div>
        @endforelse
    </div>
</section>
