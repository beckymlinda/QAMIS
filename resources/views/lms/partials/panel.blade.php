<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 px-6 py-4">
        <h3 class="text-lg font-semibold text-[#0f2744]">{{ $title }}</h3>
        @if(!empty($subtitle))
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
