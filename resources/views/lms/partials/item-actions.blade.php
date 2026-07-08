@props([
    'viewUrl' => null,
    'editAction' => null,
    'deleteUrl' => null,
    'deleteConfirm' => 'Delete this item?',
])

<div class="flex flex-wrap gap-2">
    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</a>
    @endif
    @if($editAction)
        {!! $editAction !!}
    @endif
    @if($deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm(@js($deleteConfirm));">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
        </form>
    @endif
    {{ $slot }}
</div>
