@props(['gaps', 'emptyMessage' => 'No significant gaps recorded.', 'showGuidance' => false])

@php
    use App\Services\AssessmentStrengthsAnalysis;
    $analysisService = app(AssessmentStrengthsAnalysis::class);
@endphp

<ul class="space-y-6 text-sm">
    @forelse($gaps as $response)
        @php $guidance = $showGuidance ? $analysisService->gapGuidance($response) : null; @endphp
        <li class="rounded-lg border border-red-200 bg-red-50/50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="font-medium text-[#0f2744]">{{ $response->criterion->title }}</div>
                @if($response->criterion->is_mandatory)
                    <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-700">Mandatory</span>
                @endif
            </div>
            <div class="mt-2 text-red-700 font-semibold">Score: {{ $response->score }}/4</div>

            @if($showGuidance && $guidance)
                <div class="mt-4 space-y-3">
                    <div class="rounded-md bg-white border border-red-100 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-red-800 mb-1">Why this item failed</div>
                        <p class="text-gray-700 leading-relaxed">{{ $guidance['why_failed'] }}</p>
                        @if($response->comments)
                            <p class="mt-2 text-gray-600 italic">Reviewer note: {{ $response->comments }}</p>
                        @endif
                    </div>
                    <div class="rounded-md bg-white border border-[#8cc63f]/40 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-[#0f2744] mb-1">What to do to pass</div>
                        <p class="text-gray-700 leading-relaxed">{{ $guidance['action_required'] }}</p>
                    </div>
                </div>
            @else
                @php $rubric = $response->criterion->rubricLevels?->firstWhere('score', $response->score); @endphp
                @if($rubric?->descriptor)
                    <p class="mt-2 text-gray-700">{{ $rubric->descriptor }}</p>
                @endif
                @if($response->comments)
                    <p class="mt-2 text-gray-600 italic">Reviewer note: {{ $response->comments }}</p>
                @endif
            @endif
        </li>
    @empty
        <li class="text-gray-500">{{ $emptyMessage }}</li>
    @endforelse
</ul>
