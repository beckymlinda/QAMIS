<section aria-labelledby="student-notifications-heading">
    <div class="mb-5 flex items-end justify-between gap-3">
        <div>
            <h2 id="student-notifications-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Recent Notifications</h2>
            <p class="mt-1 text-xs text-gray-500">Updates from your lecturers</p>
        </div>
        <a href="{{ route('student.notifications') }}" class="text-sm font-semibold text-[#0f2744] hover:text-[#8cc63f]">View all →</a>
    </div>

    <div class="space-y-3">
        @forelse($recentNotifications as $notification)
            <a href="{{ route('student.notifications.read', $notification) }}" class="block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-[#8cc63f]/30 {{ $notification->read_at ? 'opacity-80' : 'border-l-4 border-[#8cc63f]' }}">
                <p class="text-sm font-semibold text-[#0f2744]">{{ $notification->title }}</p>
                @if($notification->body)
                    <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ $notification->body }}</p>
                @endif
                <p class="mt-2 text-[10px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
            </a>
        @empty
            <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                <i class="bi bi-bell text-3xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 text-sm text-gray-500">No notifications yet.</p>
            </div>
        @endforelse
    </div>
</section>
