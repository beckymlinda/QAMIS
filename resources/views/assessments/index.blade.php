<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between"><h2 class="font-semibold text-xl text-gray-800">Assessments</h2>
            @can('create', App\Models\Assessment::class)
                <div class="flex gap-2">
                    <a href="{{ route('assessments.create', ['type' => 'institutional']) }}" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Institutional</a>
                    <a href="{{ route('assessments.create', ['type' => 'programme']) }}" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Programme</a>
                </div>
            @endcan
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Title</th><th>Type</th><th>Status</th><th>Compliance</th><th>Recommendation</th><th></th></tr></thead>
                <tbody>
                    @foreach ($assessments as $assessment)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $assessment->title }}</td>
                            <td class="px-4 py-3">{{ ucfirst($assessment->assessment_type) }}</td>
                            <td class="px-4 py-3">{{ $assessment->status->label() }}</td>
                            <td class="px-4 py-3">{{ $assessment->complianceResult?->compliance_status?->label() ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $assessment->complianceResult?->accreditation_recommendation?->label() ?? '—' }}</td>
                            <td class="px-4 py-3"><a href="{{ route('assessments.show', $assessment) }}" class="text-indigo-600">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $assessments->links() }}</div>
        </div>
    </div>
</x-app-layout>
