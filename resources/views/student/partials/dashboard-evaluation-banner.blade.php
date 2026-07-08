@if($period)
    <section class="overflow-hidden rounded-2xl border border-[#8cc63f]/40 bg-gradient-to-r from-[#8cc63f]/15 to-white p-6 shadow-sm" aria-labelledby="evaluation-period-heading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0f2744]">Teaching evaluation period open</p>
                <h2 id="evaluation-period-heading" class="mt-1 text-lg font-bold text-[#0f2744]">{{ $period->title }}</h2>
                <p class="mt-1 text-sm text-gray-600">Closes {{ $period->closes_at->format('d M Y, H:i') }}</p>
                @if($pendingCount > 0)
                    <p class="mt-2 text-sm text-[#0f2744]"><strong>{{ $pendingCount }}</strong> course {{ Str::plural('evaluation', $pendingCount) }} still pending.</p>
                @else
                    <p class="mt-2 text-sm font-medium text-green-700">All evaluations submitted for this period. Thank you.</p>
                @endif
            </div>
            @if($pendingCount > 0)
                <a href="{{ route('student.evaluations') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#0f2744] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3a5c] focus:outline-none focus:ring-2 focus:ring-[#8cc63f]">
                    Complete evaluations <i class="bi bi-arrow-right-short text-lg" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </section>
@endif
