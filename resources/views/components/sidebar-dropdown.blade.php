@props(['label', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button
        type="button"
        @click="open = !open"
        aria-expanded="false"
        x-bind:aria-expanded="open.toString()"
        class="{{ $active
            ? 'flex w-full items-center gap-3 rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-white/10 transition-all duration-200'
            : 'flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-blue-100/90 transition-all duration-200 hover:bg-white/10 hover:text-white hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8cc63f]/50' }}"
    >
        @if(isset($icon))
            <span class="shrink-0">{{ $icon }}</span>
        @endif
        <span class="min-w-0 flex-1 truncate text-left">{{ $label }}</span>
        <span
            class="inline-flex shrink-0 items-center justify-center text-[10px] leading-none opacity-60 transition-transform duration-200"
            x-bind:class="open ? 'rotate-180' : ''"
            aria-hidden="true"
        >▼</span>
    </button>
    <div x-show="open" x-cloak class="space-y-0.5 pb-1 pt-0.5">
        {{ $slot }}
    </div>
</div>
