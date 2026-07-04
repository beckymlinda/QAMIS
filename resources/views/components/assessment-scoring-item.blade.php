@props(['response', 'index', 'readOnly' => false])

@php
    use App\Support\DefaultScoringRubric;

    $criterion = $response->criterion;
    $rubrics = $criterion->rubricLevels->keyBy('score');
    $defaults = DefaultScoringRubric::levels();
    $selectedRubric = $response->score !== null ? $rubrics->get($response->score) : null;
@endphp

<div
    class="border rounded-lg p-4 space-y-3"
    x-data="{
        score: @js($response->score),
        rubrics: @js($rubrics->map(fn ($r) => ['score' => $r->score, 'label' => $r->level_label, 'descriptor' => $r->descriptor])->values()),
        defaults: @js(collect($defaults)->map(fn ($level, $score) => ['score' => $score, 'label' => $level['label'], 'descriptor' => $level['descriptor']])->values()),
        selectedDescriptor() {
            if (this.score === null || this.score === '') return '';
            const match = this.rubrics.find(r => r.score == this.score) || this.defaults.find(r => r.score == this.score);
            return match ? match.descriptor : '';
        },
        selectedLabel() {
            if (this.score === null || this.score === '') return '';
            const match = this.rubrics.find(r => r.score == this.score) || this.defaults.find(r => r.score == this.score);
            return match ? match.label : '';
        }
    }"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex-1">
            <div class="font-medium text-[#0f2744]">
                @if($criterion->sequence_no)
                    <span class="text-gray-500">{{ $criterion->sequence_no }}.</span>
                @endif
                {{ $criterion->title }}
                @if($criterion->is_mandatory)
                    <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-700">Mandatory</span>
                @endif
            </div>
            @if($criterion->description)
                <p class="mt-1 text-sm text-gray-600">{{ $criterion->description }}</p>
            @endif
        </div>

        @if($readOnly)
            @if($response->score !== null)
                <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm lg:max-w-md">
                    <div class="font-semibold text-[#0f2744]">{{ $response->score }} – {{ $selectedRubric?->level_label ?? ($defaults[$response->score]['label'] ?? '') }}</div>
                    <p class="mt-1 text-gray-600">{{ $selectedRubric?->descriptor ?? ($defaults[$response->score]['descriptor'] ?? '') }}</p>
                </div>
            @else
                <span class="text-sm text-gray-500">Not scored</span>
            @endif
        @else
            <div class="w-full lg:max-w-xl">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Compliance level</label>
                <select
                    name="responses[{{ $index }}][score]"
                    class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]"
                    @change="score = $event.target.value === '' ? null : parseInt($event.target.value, 10)"
                >
                    <option value="">Select compliance level…</option>
                    @foreach ([4, 3, 2, 1, 0] as $score)
                        @php
                            $rubric = $rubrics->get($score) ?? null;
                            $label = $rubric?->level_label ?? $defaults[$score]['label'];
                            $descriptor = $rubric?->descriptor ?? $defaults[$score]['descriptor'];
                        @endphp
                        <option value="{{ $score }}" @selected($response->score === $score)>
                            {{ $score }} – {{ $label }}: {{ Str::limit($descriptor, 140) }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="responses[{{ $index }}][id]" value="{{ $response->id }}">
            </div>
        @endif
    </div>

    <div x-show="score !== null && score !== ''" x-cloak class="rounded-md bg-blue-50 px-3 py-2 text-sm text-[#0f2744]">
        <span class="font-semibold" x-text="score + ' – ' + selectedLabel()"></span>
        <span x-text="': ' + selectedDescriptor()"></span>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">
            Reviewer comments / justification
        </label>
        @if($readOnly)
            <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $response->comments ?: '—' }}</p>
        @else
            <textarea
                name="responses[{{ $index }}][comments]"
                rows="3"
                class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]"
                placeholder="Provide evidence, observations, or justification for the selected compliance level…"
            >{{ old("responses.{$index}.comments", $response->comments) }}</textarea>
        @endif
    </div>

    @if($readOnly && $response->score !== null)
        @if($response->score >= 3)
            <div class="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-900">
                <span class="font-semibold">Strength:</span> This item demonstrates good performance (score {{ $response->score }}).
            </div>
        @elseif($response->score <= 2)
            <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                <span class="font-semibold">Area for improvement:</span> This item requires attention (score {{ $response->score }}).
            </div>
        @endif
    @endif
</div>
