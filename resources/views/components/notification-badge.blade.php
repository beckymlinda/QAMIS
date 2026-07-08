@props(['count' => 0])

@if($count > 0)
    <span {{ $attributes->merge(['class' => 'ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold leading-none text-white']) }}>
        {{ $count > 99 ? '99+' : $count }}
    </span>
@endif
