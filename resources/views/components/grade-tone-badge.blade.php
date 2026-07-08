@props(['tone' => 'success', 'label' => null])

@php
    $classes = \App\Support\GpaGrading::toneClasses($tone);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wide ring-1 '.$classes['badge']]) }}>
    {{ $label ?? $slot }}
</span>
