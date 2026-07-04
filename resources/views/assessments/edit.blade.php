<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Edit: {{ $assessment->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Score each section and save before submitting.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-sm bg-gray-100">{{ $assessment->status->label() }}</span>
                <a href="{{ route('assessments.show', $assessment) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">View results</a>
            </div>
        </div>
    </x-slot>
    <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @php
            $savedScoresCount = $assessment->responses->whereNotNull('score')->count();
        @endphp

        <div class="bg-[#0f2744] text-white rounded-lg p-5 shadow">
            <p class="text-sm leading-relaxed">
                Select a compliance level for each item, then click <strong>Save … Scores</strong> on that section.
                The section aggregate and overall average update only after scores are saved.
                Use <strong>Submit for Review</strong> only after you have saved the sections you have completed.
            </p>
            @if($savedScoresCount === 0)
                <p class="mt-2 text-sm text-amber-200">No saved scores yet on this assessment.</p>
            @else
                <p class="mt-2 text-sm text-[#8cc63f]">{{ $savedScoresCount }} item(s) saved so far.</p>
            @endif
        </div>

        @if($assessment->complianceResult)
            <div class="bg-white p-6 rounded-lg shadow grid md:grid-cols-4 gap-4">
                <div><div class="text-sm text-gray-500">Overall Average</div><div class="text-2xl font-bold">{{ number_format($assessment->complianceResult->overall_average, 2) }}/4</div></div>
                <div><div class="text-sm text-gray-500">Compliance</div><div class="text-lg font-semibold">{{ $assessment->complianceResult->compliance_status->label() }}</div></div>
                <div><div class="text-sm text-gray-500">Readiness</div><div class="text-lg font-semibold">{{ $assessment->complianceResult->accreditation_recommendation->label() }}</div></div>
                <div><div class="text-sm text-gray-500">Risk</div><div class="text-lg font-semibold uppercase">{{ $assessment->complianceResult->risk_level }}</div></div>
            </div>
        @endif

        @if(!$assessment->isReadOnly())
            <div class="flex flex-wrap gap-2">
                @can('transition', [$assessment, App\Enums\AssessmentStatus::Submitted])
                    <form method="POST" action="{{ route('assessments.transition', $assessment) }}">@csrf
                        <input type="hidden" name="status" value="submitted">
                        <button class="px-4 py-2 bg-[#0f2744] text-white rounded-md text-sm font-medium">
                            {{ $assessment->status === App\Enums\AssessmentStatus::Submitted ? 'Resubmit for Review' : 'Submit for Review' }}
                        </button>
                    </form>
                @endcan
                @foreach (['reviewed' => 'Mark Reviewed', 'approved' => 'Approve', 'locked' => 'Lock'] as $status => $label)
                    @can('transition', [$assessment, App\Enums\AssessmentStatus::from($status)])
                        <form method="POST" action="{{ route('assessments.transition', $assessment) }}">@csrf
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button class="px-3 py-1 bg-gray-700 text-white rounded-md text-sm">{{ $label }}</button>
                        </form>
                    @endcan
                @endforeach
            </div>
        @endif

        @php
            $templateSections = $assessment->template->sections ?? collect();

            $groupDefs = [
                ['label' => 'Governance and management', 'codes' => ['AREA-2.2', 'AREA-2.3', 'AREA-3']],
                ['label' => 'Financial resources and sustainability', 'codes' => ['AREA-4.1', 'AREA-4.2']],
                ['label' => 'Infrastructure (buildings, space and physical facilities)', 'codes' => ['AREA-5.1', 'AREA-CLASSROOMS_LABS']],
                ['label' => 'Library, learning resources and ICT', 'codes' => ['AREA-5.2', 'AREA-5.2-ICT']],
                ['label' => 'Water and sanitation facilities', 'codes' => ['AREA-WATSAN']],
                ['label' => 'Student support services', 'codes' => ['AREA-6']],
                ['label' => 'Quality assurance systems', 'codes' => ['AREA-QAS']],
                ['label' => 'Teaching, learning, research and community engagement', 'codes' => ['AREA-7', 'AREA-8', 'AREA-9']],
            ];

            $sectionResponses = function ($section) use ($assessment) {
                return $assessment->responses
                    ->whereIn('assessment_criterion_id', $section->criteria->pluck('id'))
                    ->sortBy(fn ($response) => $response->criterion?->sequence_no ?? 0)
                    ->values();
            };
        @endphp

        @if($assessment->assessment_type === 'institutional')
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm font-medium text-[#0f2744] mb-3">Jump to section</p>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="#section-area-1" class="rounded-md border border-gray-200 px-3 py-1.5 hover:bg-gray-50">Guiding Principles</a>
                    @foreach($templateSections->where('code', '!=', 'AREA-1') as $navSection)
                        <a href="#section-{{ Str::slug($navSection->code) }}" class="rounded-md border border-gray-200 px-3 py-1.5 hover:bg-gray-50">{{ $navSection->title }}</a>
                    @endforeach
                </div>
            </div>

            @foreach ($templateSections->where('code', 'AREA-1') as $section)
                <div id="section-{{ Str::slug($section->code) }}">
                    @include('assessments.partials.section-scoring-form', [
                        'assessment' => $assessment,
                        'section' => $section,
                        'responses' => $sectionResponses($section),
                        'summary' => $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id),
                        'description' => 'Guiding principles — score each item, then save this section.',
                    ])
                </div>
            @endforeach

            @foreach($groupDefs as $group)
                @php $sectionsForGroup = $templateSections->whereIn('code', $group['codes']); @endphp
                @if($sectionsForGroup->isNotEmpty())
                    <div class="pt-2">
                        <h3 class="text-lg font-semibold text-[#0f2744]">{{ $group['label'] }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Score each section below and save before moving on.</p>
                    </div>
                    @foreach ($sectionsForGroup as $section)
                        <div id="section-{{ Str::slug($section->code) }}">
                            @include('assessments.partials.section-scoring-form', [
                                'assessment' => $assessment,
                                'section' => $section,
                                'responses' => $sectionResponses($section),
                                'summary' => $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id),
                                'description' => $group['label'],
                            ])
                        </div>
                    @endforeach
                @endif
            @endforeach
        @else
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm font-medium text-[#0f2744] mb-3">Jump to section</p>
                <div class="flex flex-wrap gap-2 text-sm">
                    @foreach($templateSections as $navSection)
                        <a href="#section-{{ Str::slug($navSection->code) }}" class="rounded-md border border-gray-200 px-3 py-1.5 hover:bg-gray-50">{{ $navSection->title }}</a>
                    @endforeach
                </div>
            </div>

            @foreach ($templateSections as $section)
                <div id="section-{{ Str::slug($section->code) }}">
                    @include('assessments.partials.section-scoring-form', [
                        'assessment' => $assessment,
                        'section' => $section,
                        'responses' => $sectionResponses($section),
                        'summary' => $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id),
                    ])
                </div>
            @endforeach
        @endif

        @include('assessments.partials.recommendations-form', ['assessment' => $assessment])
    </div>
</x-app-layout>
