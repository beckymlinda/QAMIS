@props([
    'name',
    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
    'multiple' => false,
    'existingUrl' => null,
    'previewClass' => 'h-16 w-28 shrink-0 rounded-lg object-cover ring-1 ring-gray-200 bg-gray-50',
    'label' => null,
])

<div x-data="{ previews: [] }" class="space-y-2">
    @if($label)
        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <div class="flex flex-wrap items-start gap-3">
        @if($existingUrl && ! $multiple)
            <img
                src="{{ $existingUrl }}"
                alt=""
                class="{{ $previewClass }}"
                x-show="previews.length === 0"
            >
        @endif

        <template x-for="(src, index) in previews" :key="index">
            <img :src="src" alt="" class="{{ $previewClass }}">
        </template>

        <div class="min-w-0 flex-1">
            <input
                type="file"
                name="{{ $name }}"
                accept="{{ $accept }}"
                @if($multiple) multiple @endif
                @change="
                    previews.forEach(url => URL.revokeObjectURL(url));
                    previews = Array.from($event.target.files || []).map(file => URL.createObjectURL(file));
                "
                {{ $attributes->merge(['class' => 'w-full rounded-xl border-gray-300 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700']) }}
            >
        </div>
    </div>
</div>
