@props(['material', 'offering'])

@php
    $typeLabel = \App\Models\LmsMaterial::TYPES[$material->type] ?? ucfirst($material->type);
    $icon = match ($material->type) {
        'pdf' => 'bi-file-earmark-pdf',
        'presentation' => 'bi-file-earmark-slides',
        'video_link' => 'bi-play-circle',
        'audio_link' => 'bi-music-note-beamed',
        'link' => 'bi-link-45deg',
        default => 'bi-file-earmark-text',
    };
@endphp

<div class="flex flex-col gap-3 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#0f2744] shadow-sm ring-1 ring-gray-100">
            <i class="bi {{ $icon }} text-lg" aria-hidden="true"></i>
        </span>
        <div class="min-w-0">
            <p class="font-semibold text-[#0f2744]">{{ $material->title }}</p>
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                <span class="inline-flex items-center gap-1">
                    <i class="bi bi-tag" aria-hidden="true"></i>
                    {{ $typeLabel }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    Uploaded {{ $material->created_at->format('d M Y') }}
                </span>
                <span class="inline-flex items-center gap-1 text-gray-400">
                    {{ $material->created_at->format('H:i') }}
                </span>
            </div>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:pl-4">
        @if($material->isLink())
            <a href="{{ $material->external_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-[#0f2744] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1a3a5c]">
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Open
            </a>
        @elseif($material->file_path && $material->allow_download)
            <a href="{{ route('student.lms.materials.download', [$offering, $material]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#0f2744] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1a3a5c]">
                <i class="bi bi-download" aria-hidden="true"></i> Download
            </a>
        @else
            <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-500 ring-1 ring-gray-200">View only</span>
        @endif
    </div>
</div>
