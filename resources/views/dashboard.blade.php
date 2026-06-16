<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">QAMIS Dashboard</h2>
            <form action="{{ route('search') }}" method="GET" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search..." class="rounded-md border-gray-300 text-sm">
                <button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Search</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ([
                    'programmes' => 'Programmes',
                    'assessments' => 'Assessments',
                    'evidence' => 'Evidence',
                    'staff' => 'Staff',
                    'reports' => 'Reports',
                    'outstanding_actions' => 'Open Actions',
                ] as $key => $label)
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-bold text-indigo-700">{{ $stats[$key] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>

            @if ($cache)
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Compliance Overview</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Overall Compliance</div>
                            <div class="text-3xl font-bold text-green-600">{{ $cache->overall_compliance_pct }}%</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Risk Level</div>
                            <div class="text-xl font-semibold uppercase">{{ $cache->risk_level ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Evidence Completeness</div>
                            <div class="text-xl font-semibold">{{ $cache->evidence_completeness_pct ?? 0 }}%</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Recent Assessments</h3>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Compliance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentAssessments as $assessment)
                            <tr class="border-b">
                                <td class="py-2">{{ $assessment->title }}</td>
                                <td>{{ ucfirst($assessment->assessment_type) }}</td>
                                <td>{{ $assessment->status->label() }}</td>
                                <td>{{ $assessment->complianceResult?->compliance_status?->label() ?? '—' }}</td>
                                <td><a href="{{ route('assessments.show', $assessment) }}" class="text-indigo-600">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500">No assessments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
