@php
    use App\Support\ApplicationDocuments;
    $documentTypes = ApplicationDocuments::types();
    $existingPaths = isset($application) && $application ? $application->documentPaths() : [];
    $isEdit = ! empty($application);
@endphp

<div class="space-y-4" x-data="{ docType: '' }">
    <div>
        <h3 class="text-base font-bold text-gray-900">Upload documents</h3>
        <p class="text-xs text-gray-500">Select a document type from the dropdown, then choose the file. Max {{ $maxUploadMb }} MB each.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Which document are you uploading?</label>
            <select x-model="docType" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                <option value="">Choose document type…</option>
                @foreach($documentTypes as $field => $meta)
                    <option value="{{ $field }}">{{ $meta['label'] }}{{ $meta['required'] && ! $isEdit ? ' *' : '' }}</option>
                @endforeach
            </select>
        </div>

        @foreach($documentTypes as $field => $meta)
            <div x-show="docType === '{{ $field }}'" x-cloak class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                <label class="text-sm font-medium text-gray-800">{{ $meta['label'] }}</label>
                <input
                    type="file"
                    name="{{ $field }}"
                    accept="{{ $meta['accept'] }}"
                    @if($meta['required'] && ! $isEdit) required @endif
                    class="mt-2 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90"
                    style="--tw-file-bg: var(--brand-primary, #0f2744)"
                >
                @if($isEdit && ($existingPaths[$field] ?? null))
                    <p class="mt-2 text-xs text-gray-500">Uploading a new file will replace the current one.</p>
                @endif
            </div>
        @endforeach

        <p class="text-xs text-gray-500">
            @if($isEdit)
                Required documents must remain on file. Replace any removed document before saving.
            @else
                <span class="font-semibold">Required:</span> Academic certificates, Examination results, Proof of application fee.
            @endif
        </p>
    </div>
</div>
