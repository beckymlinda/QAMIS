<table class="min-w-full text-sm">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-3 text-left w-12">#</th>
            <th class="px-4 py-3 text-left">Title</th>
            <th class="px-4 py-3 text-left">Date</th>
            @if($showType ?? false)
                <th class="px-4 py-3 text-left">Type</th>
            @endif
            @if($showProgramme ?? false)
                <th class="px-4 py-3 text-left">Programme</th>
            @endif
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Compliance</th>
            <th class="px-4 py-3 text-left">Readiness</th>
            <th class="px-4 py-3 text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($assessments as $assessment)
            <tr class="border-t">
                <td class="px-4 py-3 text-gray-500 font-medium">{{ ($assessments->firstItem() ?? 0) + $loop->index }}</td>
                <td class="px-4 py-3 font-medium text-[#0f2744]">{{ $assessment->title }}</td>
                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                    @if($assessment->period_start)
                        {{ $assessment->period_start->format('d M Y') }}
                        @if($assessment->period_end)
                            <span class="text-gray-400">–</span> {{ $assessment->period_end->format('d M Y') }}
                        @endif
                    @else
                        {{ $assessment->created_at->format('d M Y') }}
                    @endif
                </td>
                @if($showType ?? false)
                    <td class="px-4 py-3">{{ ucfirst($assessment->assessment_type) }}</td>
                @endif
                @if($showProgramme ?? false)
                    <td class="px-4 py-3">{{ $assessment->programme?->name ?? '—' }}</td>
                @endif
                <td class="px-4 py-3">{{ $assessment->status->label() }}</td>
                <td class="px-4 py-3">{{ $assessment->complianceResult?->compliance_status?->label() ?? '—' }}</td>
                <td class="px-4 py-3">{{ $assessment->complianceResult?->accreditation_recommendation?->label() ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('assessments.show', $assessment) }}" class="font-medium text-[#0f2744] hover:text-[#8cc63f]">Open</a>
                        @can('update', $assessment)
                            <a href="{{ route('assessments.edit', $assessment) }}" class="font-medium text-[#0f2744] hover:text-[#8cc63f]">Edit</a>
                        @endcan
                        @can('delete', $assessment)
                            <form method="POST" action="{{ route('assessments.destroy', $assessment) }}" onsubmit="return confirm('Delete this assessment? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 7 + (($showType ?? false) ? 1 : 0) + (($showProgramme ?? false) ? 1 : 0) }}" class="px-4 py-8 text-center text-gray-500">
                    {{ $emptyMessage ?? 'No assessments yet.' }}
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
