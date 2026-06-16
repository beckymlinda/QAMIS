<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ $programme->name }}</h2></x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-6 rounded-lg shadow"><p><strong>Level:</strong> {{ $programme->level }}</p><p><strong>Department:</strong> {{ $programme->orgUnit?->name ?? '—' }}</p><p><strong>Accreditation:</strong> {{ $programme->nche_accreditation_status }}</p></div>
        <div class="bg-white p-6 rounded-lg shadow"><h3 class="font-semibold mb-2">Assessments</h3>@forelse($programme->assessments as $a)<p><a href="{{ route('assessments.show', $a) }}" class="text-indigo-600">{{ $a->title }}</a> — {{ $a->complianceResult?->compliance_status?->label() ?? 'Pending' }}</p>@empty<p class="text-gray-500">No programme assessments yet.</p>@endforelse</div>
    </div>
</x-app-layout>
