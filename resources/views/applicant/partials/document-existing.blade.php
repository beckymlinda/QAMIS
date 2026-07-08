@php
    use App\Support\ApplicationDocuments;
    $documentTypes = ApplicationDocuments::types();
    $existingPaths = $application->documentPaths();
@endphp

<div class="space-y-2">
    @foreach($documentTypes as $field => $meta)
        @if($existingPaths[$field] ?? null)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $meta['label'] }}</p>
                    <p class="text-xs text-green-700">Currently uploaded</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('applicant.applications.file.preview', [$application, $field]) }}" target="_blank" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-brand-primary ring-1 ring-gray-200 hover:bg-gray-50">Preview</a>
                    <a href="{{ route('applicant.applications.file', [$application, $field]) }}" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">Download</a>
                    <form method="POST" action="{{ route('applicant.applications.documents.remove', [$application, $field]) }}" onsubmit="return confirm('Remove this file? You can upload a replacement in the form below.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200 hover:bg-red-100">Remove</button>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</div>
