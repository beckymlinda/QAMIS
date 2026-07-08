<div class="mx-auto max-w-4xl space-y-3 px-4 py-8 sm:px-6 lg:px-8">
    @forelse($notifications as $notification)
        <a
            href="{{ route($readRoute, $notification) }}"
            class="block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-[#8cc63f]/40 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-[#8cc63f]' }}"
        >
            <p class="font-medium text-[#0f2744]">{{ $notification->title }}</p>
            @if($notification->body)
                <p class="mt-1 text-sm text-gray-600">{{ $notification->body }}</p>
            @endif
            <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
        </a>
    @empty
        <div class="rounded-2xl bg-white p-12 text-center text-gray-500 shadow-sm ring-1 ring-gray-100">
            No notifications yet.
        </div>
    @endforelse

    {{ $notifications->links() }}
</div>
