<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'evaluations' && $errors->any() ? 'true' : 'false' }} }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Teaching evaluations</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $evaluationPeriods->count() }} evaluation period{{ $evaluationPeriods->count() === 1 ? '' : 's' }} · NCHE student questionnaire (Course A, Lecturer B, open-ended)</p>
        </div>
        <button
            type="button"
            x-show="!showForm"
            @click="showForm = true"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
        >Open evaluation period</button>
    </div>

    <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
        <h4 class="text-sm font-semibold text-[#0f2744] mb-1">New evaluation period</h4>
        <p class="text-xs text-gray-500 mb-4">Enrolled students can rate courses and lecturers during this window.</p>

        <form method="POST" action="{{ route('programmes.evaluation-periods.store', $programme) }}" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm items-end">
            @csrf
            <input type="hidden" name="tab" value="evaluations">
            <div class="sm:col-span-2 lg:col-span-3"><label class="block text-gray-600 mb-1">Title</label><input name="title" required value="{{ old('title', 'End of Semester Teaching Evaluation') }}" class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Academic year</label><input name="academic_year" value="{{ old('academic_year', $currentYear) }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Semester</label><input name="semester" type="number" min="1" max="3" value="{{ old('semester', 1) }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Opens</label><input name="opens_at" type="datetime-local" value="{{ old('opens_at') }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Closes</label><input name="closes_at" type="datetime-local" value="{{ old('closes_at') }}" required class="w-full rounded-md border-gray-300"></div>
            <div class="sm:col-span-2 lg:col-span-3 flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Open period</button>
                <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-3 py-2 text-left">Period</th>
                <th class="px-3 py-2 text-left">Year / Sem</th>
                <th class="px-3 py-2 text-left">Opens</th>
                <th class="px-3 py-2 text-left">Closes</th>
                <th class="px-3 py-2 text-left">Responses</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($evaluationPeriods as $period)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-medium">{{ $period->title }}</td>
                        <td class="px-3 py-2">{{ $period->academic_year }} / Sem {{ $period->semester }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $period->opens_at->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $period->closes_at->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2">{{ $period->submitted_count ?? 0 }}</td>
                        <td class="px-3 py-2">
                            @if($period->isOpen())
                                <span class="text-green-600 font-medium">Open now</span>
                            @else
                                <span class="text-gray-500">Closed</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('programmes.evaluation-periods.show', [$programme, $period]) }}" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f] mr-3">View</a>
                            <a href="{{ route('programmes.evaluation-periods.report', [$programme, $period]) }}" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f]">Generate report</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-gray-500">No evaluation periods yet. Click <strong>Open evaluation period</strong> to create one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
