<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ $programme->name }}</h2></x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-6 rounded-lg shadow"><p><strong>Level:</strong> {{ $programme->level }}</p><p><strong>Department:</strong> {{ $programme->orgUnit?->name ?? '—' }}</p><p><strong>Accreditation:</strong> {{ $programme->nche_accreditation_status }}</p></div>

        @can('update', $programme)
            <div class="rounded-lg border border-[#8cc63f]/40 bg-[#8cc63f]/10 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-[#0f2744]">Course & student management</h3>
                    <p class="text-sm text-gray-600 mt-1">Add courses, lecturers, students, timetables, and open teaching evaluations for the student portal.</p>
                </div>
                <a href="{{ route('programmes.academic.index', $programme) }}" class="shrink-0 rounded-lg bg-[#0f2744] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1a3a5c]">
                    Manage courses & students
                </a>
            </div>
        @endcan

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold mb-4 text-[#0f2744]">Programme assessments</h3>
            @forelse($programme->assessments as $a)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-gray-100 py-3 last:border-0 text-sm">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#0f2744]/10 text-xs font-semibold text-[#0f2744]">{{ $loop->iteration }}</span>
                    <a href="{{ route('assessments.show', $a) }}" class="font-medium text-[#0f2744] hover:text-[#8cc63f]">{{ $a->title }}</a>
                    <span class="text-gray-500">
                        @if($a->period_start)
                            {{ $a->period_start->format('d M Y') }}
                            @if($a->period_end)
                                – {{ $a->period_end->format('d M Y') }}
                            @endif
                        @else
                            {{ $a->created_at->format('d M Y') }}
                        @endif
                    </span>
                    <span class="text-gray-600">{{ $a->complianceResult?->compliance_status?->label() ?? 'Pending' }}</span>
                    @can('update', $a)
                        <a href="{{ route('assessments.edit', $a) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">Edit</a>
                    @endcan
                </div>
            @empty
                <p class="text-gray-500">No programme assessments yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
