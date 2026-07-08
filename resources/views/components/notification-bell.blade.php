@props([
    'href',
    'count' => null,
])

@php
    $unread = $count ?? (auth()->check()
        ? app(\App\Services\LmsService::class)->unreadNotifications(auth()->user())
        : 0);
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'relative inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-visible rounded-xl text-[#0f2744] transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#8cc63f]']) }}
    aria-label="Notifications{{ $unread > 0 ? ' ('.$unread.' unread)' : '' }}"
>
    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>

    @if($unread > 0)
        <span
            class="absolute right-0 top-0 z-20 flex h-[18px] min-w-[18px] items-center justify-center rounded-full border-2 border-white bg-red-600 px-1 text-[10px] font-bold leading-none text-white shadow"
            aria-hidden="true"
        >
            {{ $unread > 99 ? '99+' : $unread }}
        </span>
    @endif
</a>
