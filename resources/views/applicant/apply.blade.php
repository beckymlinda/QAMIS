<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">Admissions</p>
            <h2 class="text-xl font-bold text-brand-primary">New application — {{ $website->displayName() }}</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        @if($programmes->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <p class="font-semibold">Applications are closed</p>
                <p class="mt-1 text-sm">No programmes are currently open for applications.</p>
            </div>
        @else
            @include('applicant.partials.application-form', [
                'website' => $website,
                'programmes' => $programmes,
                'maxUploadMb' => $maxUploadMb,
                'user' => $user,
                'application' => null,
                'gradeData' => $gradeData,
            ])
        @endif
    </div>
</x-app-layout>
