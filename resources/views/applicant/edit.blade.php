<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">Edit application</p>
                <h2 class="text-xl font-bold text-brand-primary">{{ $application->application_number }}</h2>
            </div>
            <a href="{{ route('applicant.applications.show', $application) }}" class="text-sm font-semibold text-brand-primary">← View application</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            You can update your application while the programme application window is open. Changes are saved when you click <strong>Save changes</strong>.
        </div>

        @php
            $hasExistingDocs = collect($application->documentPaths())->filter()->isNotEmpty();
        @endphp
        @if($hasExistingDocs)
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-base font-bold text-gray-900">Current documents</h3>
                <p class="mt-1 text-xs text-gray-500">Preview, download, or remove files before uploading replacements below.</p>
                <div class="mt-4">
                    @include('applicant.partials.document-existing', ['application' => $application])
                </div>
            </section>
        @endif

        @include('applicant.partials.application-form', [
            'website' => $website,
            'programmes' => $programmes,
            'maxUploadMb' => $maxUploadMb,
            'user' => auth()->user(),
            'application' => $application,
            'gradeData' => $gradeData,
        ])
    </div>
</x-app-layout>
