<section aria-labelledby="upcoming-heading">
    <div class="mb-5">
        <h2 id="upcoming-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Upcoming Classes</h2>
        <p class="mt-1 text-xs text-gray-500">Timeline of your next sessions</p>
    </div>

    <div class="space-y-4">
        @forelse($upcomingSlots as $slot)
            @php
                $slotOffering = $slot->courseOffering;
                $isToday = (int) $slot->day_of_week === $todayDow;
                $studentCount = $slotOffering->studentEnrolments->count();
            @endphp
            <article class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                @if($isToday)
                    <span class="absolute right-4 top-4 rounded-full bg-[#8cc63f] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#0f2744]">Today</span>
                @endif
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-[#0f2744] text-white">
                            <span class="text-[10px] font-bold uppercase">{{ Str::substr($slot->dayName(), 0, 3) }}</span>
                            <span class="text-sm font-bold">{{ substr($slot->start_time, 0, 5) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#8cc63f]">{{ $slotOffering->course->code }}</p>
                            <h3 class="mt-1 text-lg font-bold text-[#0f2744]">{{ $slotOffering->course->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-1"><i class="bi bi-clock" aria-hidden="true"></i> {{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</span>
                                <span class="inline-flex items-center gap-1"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $slot->venueLabel() }}</span>
                                <span class="inline-flex items-center gap-1"><i class="bi bi-people" aria-hidden="true"></i> {{ $studentCount }} students</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('lecturer.lms.show', $slotOffering) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a3a5c] focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
                        Open course <i class="bi bi-arrow-right-short text-lg" aria-hidden="true"></i>
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center shadow-md">
                <i class="bi bi-calendar-x text-3xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 text-sm text-gray-500">No timetable slots scheduled yet.</p>
            </div>
        @endforelse
    </div>
</section>
