<div class="bg-white rounded-lg shadow p-6" x-data="{
    items: {{ json_encode(old('recommendations', $assessment->narrative_recommendations ?? [''])) }},
    addItem() { this.items.push(''); },
    removeItem(index) { this.items.splice(index, 1); if (this.items.length === 0) this.items.push(''); }
}">
    <h3 class="text-lg font-semibold text-[#0f2744]">Recommendations</h3>
    <p class="text-sm text-gray-500 mt-1 mb-4">
        Add narrative recommendations for this {{ $assessment->assessment_type === 'programme' ? 'programme' : 'institutional' }} assessment.
        These appear as bullet points in the Self-Assessment Report (separate from the score-table recommendations).
    </p>

    <form method="POST" action="{{ route('assessments.recommendations.update', $assessment) }}">
        @csrf
        @method('PUT')

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="flex gap-2 items-start">
                    <span class="mt-2 text-sm font-medium text-gray-400 w-6" x-text="index + 1 + '.'"></span>
                    <textarea
                        :name="'recommendations[' + index + ']'"
                        x-model="items[index]"
                        rows="2"
                        class="flex-1 rounded-md border-gray-300 text-sm"
                        placeholder="Enter a recommendation..."
                    ></textarea>
                    <button type="button" @click="removeItem(index)" class="mt-1 text-sm text-red-600 hover:text-red-800" x-show="items.length > 1">Remove</button>
                </div>
            </template>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" @click="addItem()" class="px-4 py-2 border border-[#0f2744] text-[#0f2744] rounded-md text-sm font-medium hover:bg-gray-50">
                Add another recommendation
            </button>
            @if(!$assessment->isReadOnly())
                <button type="submit" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] rounded-md text-sm font-semibold hover:bg-[#7ab833]">
                    Save recommendations
                </button>
            @endif
        </div>
    </form>
</div>
