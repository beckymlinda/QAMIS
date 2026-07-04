@props(['href' => '#', 'variant' => 'ghost', 'type' => 'button', 'confirm' => null])

@php
    $classes = match ($variant) {
        'primary' => 'bg-[#0f2744] text-white hover:bg-[#1a3a5c]',
        'accent' => 'bg-[#8cc63f] text-[#0f2744] hover:bg-[#7ab535]',
        'danger' => 'bg-red-50 text-red-700 hover:bg-red-100',
        default => 'bg-gray-50 text-[#0f2744] hover:bg-gray-100 ring-1 ring-gray-200',
    };
@endphp

@if($type === 'submit' || str_starts_with($attributes->get('formaction', ''), 'http') || $attributes->has('formaction'))
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {$classes}"]) }} @if($confirm) onclick="return confirm(@js($confirm))" @endif>
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {$classes}"]) }}>
        {{ $slot }}
    </a>
@endif
