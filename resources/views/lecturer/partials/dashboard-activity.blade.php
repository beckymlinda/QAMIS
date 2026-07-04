<section aria-labelledby="activity-heading">
    <div class="mb-5">
        <h2 id="activity-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Recent Activity</h2>
        <p class="mt-1 text-xs text-gray-500">Latest updates across your courses</p>
    </div>

    <div class="rounded-2xl bg-white shadow-md">
        <ul class="divide-y divide-gray-100">
            @forelse($recentActivity as $item)
                <li class="flex items-start gap-4 px-5 py-4 transition hover:bg-gray-50/80">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0f2744]/5 text-[#0f2744]">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-[#0f2744]">{{ $item['label'] }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ $item['meta'] }} · {{ $item['at']?->diffForHumans() }}</p>
                    </div>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-sm text-gray-500">No recent activity yet. Activity will appear as you grade, enrol students, and publish announcements.</li>
            @endforelse
        </ul>
    </div>
</section>
