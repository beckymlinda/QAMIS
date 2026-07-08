@props([
    'user',
    'size' => 'md',
    'ring' => false,
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
        'xl' => 'h-24 w-24 text-2xl',
        default => 'h-10 w-10 text-sm',
    };
    $ringClass = $ring ? 'ring-2 ring-white ring-offset-1' : '';
@endphp

@if($user && $user->hasProfilePhoto())
    <img
        src="{{ $user->profilePhotoUrl() }}"
        alt="{{ $user->name }} profile photo"
        {{ $attributes->merge(['class' => "shrink-0 rounded-full object-cover {$sizeClasses} {$ringClass}"]) }}
    >
@else
    <span
        {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#0f2744] to-[#1a3a5c] font-bold text-white {$sizeClasses} {$ringClass}"]) }}
        aria-hidden="true"
    >
        {{ $user?->initials() ?? '?' }}
    </span>
@endif
