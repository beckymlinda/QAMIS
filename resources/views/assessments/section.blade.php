<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">{{ $section->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $assessment->title }}</p>
            </div>
            <a href="{{ route('assessments.show', $assessment) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to results</a>
        </div>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @if($summary && $scoredCount > 0)
            <div class="bg-white p-6 rounded-lg shadow grid md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Section aggregate</div>
                    <div class="text-2xl font-bold text-[#0f2744]">{{ number_format($summary->aggregate_score, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total score</div>
                    <div class="text-xl font-semibold">{{ $summary->total_score }} ÷ {{ $summary->divisor }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Items scored</div>
                    <div class="text-xl font-semibold">{{ $scoredCount }} / {{ $responses->count() }}</div>
                </div>
            </div>
        @endif

        @if($hasGapSection)
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-600">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Significant Gaps Identified</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Items in this section scored below the required standard. Review why each item failed and what is needed to pass.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">
                        {{ $significantGaps->count() }} gap(s)
                    </span>
                </div>
                @include('assessments.partials.significant-gaps-list', [
                    'gaps' => $significantGaps,
                    'showGuidance' => true,
                    'emptyMessage' => 'No significant gaps in this section.',
                ])
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-[#8cc63f]">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-2">Strengths (scores 3–4)</h3>
                <p class="text-sm text-gray-500 mb-4">Areas where your institution performed well in this section.</p>
                <ul class="space-y-4 text-sm">
                    @forelse($strengths as $response)
                        <li class="border-b border-gray-100 pb-3">
                            <div class="font-medium text-[#0f2744]">{{ $response->criterion->title }}</div>
                            <div class="mt-1 text-gray-600">Score: <strong>{{ $response->score }}</strong></div>
                            @if($response->comments)
                                <p class="mt-2 text-gray-700">{{ $response->comments }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-gray-500">No strengths recorded in this section yet.</li>
                    @endforelse
                </ul>
            </div>

            @unless($hasGapSection)
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-amber-500">
                    <h3 class="text-lg font-semibold text-[#0f2744] mb-2">Areas for improvement (scores 0–2)</h3>
                    <p class="text-sm text-gray-500 mb-4">Gaps that need attention and corrective action.</p>
                    <ul class="space-y-4 text-sm">
                        @forelse($improvements as $response)
                            <li class="border-b border-gray-100 pb-3">
                                <div class="font-medium text-[#0f2744]">{{ $response->criterion->title }}</div>
                                <div class="mt-1 text-gray-600">Score: <strong>{{ $response->score }}</strong></div>
                                @if($response->comments)
                                    <p class="mt-2 text-gray-700">{{ $response->comments }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-gray-500">No improvement areas in this section.</li>
                        @endforelse
                    </ul>
                </div>
            @endunless
        </div>

        @can('update', $assessment)
            <a href="{{ route('assessments.edit', $assessment) }}" class="inline-flex px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md hover:bg-[#7ab535]">
                Edit this assessment
            </a>
        @endcan
    </div>
</x-app-layout>
