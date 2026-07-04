<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">{{ $assessment->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Self-assessment results overview</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full text-sm bg-gray-100">{{ $assessment->status->label() }}</span>
                @can('update', $assessment)
                    <a href="{{ route('assessments.edit', $assessment) }}" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] text-sm font-semibold rounded-md hover:bg-[#7ab535]">
                        Edit assessment
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @if($assessment->complianceResult)
            <div class="bg-white p-6 rounded-lg shadow grid md:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Overall Average</div>
                    <div class="text-2xl font-bold text-[#0f2744]">{{ number_format($assessment->complianceResult->overall_average, 2) }}/4</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Compliance</div>
                    <div class="text-lg font-semibold">{{ $assessment->complianceResult->compliance_status->label() }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Readiness</div>
                    <div class="text-lg font-semibold">{{ $assessment->complianceResult->accreditation_recommendation->label() }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Risk</div>
                    <div class="text-lg font-semibold uppercase">{{ $assessment->complianceResult->risk_level }}</div>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg p-4 text-sm">
                No compliance summary yet. @can('update', $assessment)<a href="{{ route('assessments.edit', $assessment) }}" class="font-semibold underline">Edit the assessment</a> to score sections and submit.@endcan
            </div>
        @endif

        @if($analysis['strengths']->isNotEmpty() || $analysis['areas_for_improvement']->isNotEmpty())
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-[#8cc63f]">
                    <h3 class="text-lg font-semibold text-[#0f2744] mb-2">Overall strengths</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ $analysis['strengths']->count() }} item(s) scored 3–4</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-amber-500">
                    <h3 class="text-lg font-semibold text-[#0f2744] mb-2">Overall areas for improvement</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ $analysis['areas_for_improvement']->count() }} item(s) scored 0–2 — open each section to view gap details</p>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#0f2744]">Section scores</h3>
                <p class="text-sm text-gray-500 mt-1">Review each section aggregate and open details for strengths and improvement areas.</p>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Section</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Items scored</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Total score</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Aggregate</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sectionRows as $row)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-3 font-medium text-[#0f2744]">{{ $row['section']->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row['scored_count'] }} / {{ $row['total_items'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row['summary']?->total_score ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($row['scored_count'] > 0 && $row['summary'])
                                    <span class="inline-flex items-center rounded-full bg-[#0f2744]/10 px-2.5 py-0.5 font-semibold text-[#0f2744]">
                                        {{ number_format($row['summary']->aggregate_score, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Not scored</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($row['scored_count'] > 0)
                                    <a href="{{ route('assessments.sections.show', [$assessment, $row['section']]) }}" class="font-medium text-[#0f2744] hover:text-[#8cc63f]">View</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No sections found for this assessment template.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('update', $assessment)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('assessments.edit', $assessment) }}" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md hover:bg-[#7ab535]">
                    Continue editing
                </a>
                @if($assessment->status !== App\Enums\AssessmentStatus::Draft)
                    @can('transition', [$assessment, App\Enums\AssessmentStatus::Submitted])
                        <form method="POST" action="{{ route('assessments.transition', $assessment) }}">@csrf
                            <input type="hidden" name="status" value="submitted">
                            <button class="px-4 py-2 bg-[#0f2744] text-white rounded-md text-sm font-medium">Resubmit for review</button>
                        </form>
                    @endcan
                @endif
            </div>
        @endcan
    </div>
</x-app-layout>
