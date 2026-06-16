<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $assessment->title }}</h2>
            <span class="px-3 py-1 rounded-full text-sm bg-gray-100">{{ $assessment->status->label() }}</span>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @if($assessment->complianceResult)
            <div class="bg-white p-6 rounded-lg shadow grid md:grid-cols-4 gap-4">
                <div><div class="text-sm text-gray-500">Overall Average</div><div class="text-2xl font-bold">{{ $assessment->complianceResult->overall_average }}/4</div></div>
                <div><div class="text-sm text-gray-500">Compliance</div><div class="text-lg font-semibold">{{ $assessment->complianceResult->compliance_status->label() }}</div></div>
                <div><div class="text-sm text-gray-500">Recommendation</div><div class="text-lg font-semibold">{{ $assessment->complianceResult->accreditation_recommendation->label() }}</div></div>
                <div><div class="text-sm text-gray-500">Risk</div><div class="text-lg font-semibold uppercase">{{ $assessment->complianceResult->risk_level }}</div></div>
            </div>
        @endif

        @if(!$assessment->isReadOnly())
            <div class="flex flex-wrap gap-2">
                @foreach (['submitted' => 'Submit', 'reviewed' => 'Mark Reviewed', 'approved' => 'Approve', 'locked' => 'Lock'] as $status => $label)
                    @can('transition', [$assessment, App\Enums\AssessmentStatus::from($status)])
                        <form method="POST" action="{{ route('assessments.transition', $assessment) }}">@csrf
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">{{ $label }}</button>
                        </form>
                    @endcan
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('assessments.score', $assessment) }}">
            @csrf
            @php $responseIndex = 0; @endphp
            @foreach ($assessment->template->sections as $section)
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $section->title }} (÷{{ $section->divisor }})</h3>
                    @php $summary = $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id); @endphp
                    @if($summary)<p class="text-sm text-indigo-600 mb-4">Aggregate: {{ $summary->aggregate_score }}</p>@endif
                    <div class="space-y-4">
                        @foreach ($assessment->responses->whereIn('assessment_criterion_id', $section->criteria->pluck('id')) as $response)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $response->criterion->title }} @if($response->criterion->is_mandatory)<span class="text-red-600">*</span>@endif</div>
                                    </div>
                                    <select name="responses[{{ $responseIndex }}][score]" class="rounded-md border-gray-300 w-20" @disabled($assessment->isReadOnly())>
                                        <option value="">—</option>
                                        @for($s=0;$s<=4;$s++)<option value="{{ $s }}" @selected($response->score===$s)>{{ $s }}</option>@endfor
                                    </select>
                                    <input type="hidden" name="responses[{{ $responseIndex }}][id]" value="{{ $response->id }}">
                                </div>
                                @unless($assessment->isReadOnly())
                                    <textarea name="responses[{{ $responseIndex }}][comments]" rows="2" class="mt-2 w-full rounded-md border-gray-300 text-sm" placeholder="Comments">{{ $response->comments }}</textarea>
                                    <textarea name="responses[{{ $responseIndex }}][strengths]" rows="2" class="mt-2 w-full rounded-md border-gray-300 text-sm" placeholder="Strengths">{{ $response->strengths }}</textarea>
                                    <textarea name="responses[{{ $responseIndex }}][areas_for_improvement]" rows="2" class="mt-2 w-full rounded-md border-gray-300 text-sm" placeholder="Areas for improvement">{{ $response->areas_for_improvement }}</textarea>
                                    <textarea name="responses[{{ $responseIndex }}][recommendations]" rows="2" class="mt-2 w-full rounded-md border-gray-300 text-sm" placeholder="Recommendations">{{ $response->recommendations }}</textarea>
                                @endunless
                            </div>
                            @php $responseIndex++; @endphp
                        @endforeach
                    </div>
                </div>
            @endforeach
            @can('score', $assessment)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save Scores</button>
            @endcan
        </form>
    </div>
</x-app-layout>
