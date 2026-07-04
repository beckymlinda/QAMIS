<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#0f2744]">Notifications</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
        @foreach($notifications as $notification)
            <a href="{{ route('student.lms.notifications.read', $notification) }}" class="block bg-white rounded-lg shadow p-4 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-[#8cc63f]' }}">
                <p class="font-medium text-[#0f2744]">{{ $notification->title }}</p>
                @if($notification->body)<p class="text-sm text-gray-600 mt-1">{{ $notification->body }}</p>@endif
                <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
            </a>
        @endforeach
        {{ $notifications->links() }}
    </div>
</x-app-layout>
