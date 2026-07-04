@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-xl border-l-4 border-[#8cc63f] bg-[#8cc63f]/15 px-4 py-3 text-sm font-semibold text-[#8cc63f] shadow-sm transition-all duration-200'
    : 'flex items-center gap-3 rounded-xl border-l-4 border-transparent px-4 py-3 text-sm font-medium text-blue-100/90 transition-all duration-200 hover:border-[#8cc63f]/30 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#8cc63f]/50';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
