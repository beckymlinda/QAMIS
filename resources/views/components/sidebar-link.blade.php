@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-xl border-l-4 border-brand-secondary bg-brand-secondary/15 px-4 py-3 text-sm font-semibold text-brand-secondary shadow-sm transition-all duration-200'
    : 'flex items-center gap-3 rounded-xl border-l-4 border-transparent px-4 py-3 text-sm font-medium text-blue-100/90 transition-all duration-200 hover:border-brand-secondary/30 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-secondary/50';
@endphp

<a {{ $attributes->merge(['class' => $classes, 'target' => '_self']) }}>
    {{ $slot }}
</a>
