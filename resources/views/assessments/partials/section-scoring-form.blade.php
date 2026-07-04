@props(['assessment', 'section', 'responses', 'summary' => null, 'description' => null])

@php
    $scoredCount = $responses->filter(fn ($response) => $response->score !== null)->count();
@endphp

<div class="bg-white p-6 rounded-lg shadow mb-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold">{{ $section->title }} (÷{{ $section->divisor }})</h3>
            @if($description)
                <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
            @endif
        </div>
        @if($summary && $scoredCount > 0)
            <div class="rounded-lg bg-[#0f2744]/5 px-4 py-2 text-sm">
                <div class="font-semibold text-[#0f2744]">
                    Section aggregate: {{ number_format($summary->aggregate_score, 2) }}
                </div>
                <div class="text-gray-600">
                    Total {{ $summary->total_score }} ÷ {{ $summary->divisor }} · {{ $scoredCount }} item(s) scored
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500">No scores saved for this section yet.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('assessments.score', $assessment) }}" class="space-y-4">
        @csrf
        @foreach ($responses as $index => $response)
            <x-assessment-scoring-item
                :response="$response"
                :index="$index"
                :read-only="$assessment->isReadOnly()"
            />
        @endforeach

        @can('score', $assessment)
            @unless($assessment->isReadOnly())
                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
                    <button type="submit" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md hover:bg-[#7ab535]">
                        Save {{ $section->title }} Scores
                    </button>
                </div>
            @endunless
        @endcan
    </form>
</div>
