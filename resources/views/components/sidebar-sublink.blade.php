@props(['active' => false])

@php
$classes = $active
    ? 'block rounded-md py-1.5 pl-10 pr-3 text-sm font-medium text-[#8cc63f] bg-[#8cc63f]/10'
    : 'block rounded-md py-1.5 pl-10 pr-3 text-sm text-blue-200/90 transition-colors hover:bg-[#1a3a5c]/70 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes, 'target' => '_self']) }}>
    {{ $slot }}
</a>
