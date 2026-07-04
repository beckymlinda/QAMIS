<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-[#0f2744]">Self-Assessments</h2>
            <p class="mt-1 text-sm text-gray-600">Score your institution and programmes against NCHE accreditation tools, then review compliance and generate reports.</p>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @can('create', App\Models\Assessment::class)
            <div class="rounded-lg border border-[#8cc63f]/40 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-[#0f2744]">Start a new self-assessment</h3>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-600">
                    Select the type of assessment you want to begin below. Click
                    <strong class="text-[#0f2744]">Start institution assessment</strong> to evaluate your whole institution, or
                    <strong class="text-[#0f2744]">Start programme assessment</strong> to assess a specific academic programme.
                </p>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <a
                        href="{{ route('assessments.create', ['type' => 'institutional']) }}"
                        class="group flex h-full flex-col rounded-xl border-2 border-[#0f2744]/15 bg-[#0f2744]/5 p-5 transition hover:border-[#0f2744] hover:shadow-md"
                    >
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#0f2744] text-sm font-bold text-white">I</span>
                            <div>
                                <h4 class="font-semibold text-[#0f2744]">Institution assessment</h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    Assess governance, resources, student support, and other institutional areas using the NCHE institutional accreditation tool.
                                </p>
                            </div>
                        </div>
                        <span class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white transition group-hover:bg-[#1a3a5c]">
                            Start institution assessment
                        </span>
                    </a>

                    <a
                        href="{{ route('assessments.create', ['type' => 'programme']) }}"
                        class="group flex h-full flex-col rounded-xl border-2 border-[#8cc63f]/40 bg-[#8cc63f]/10 p-5 transition hover:border-[#8cc63f] hover:shadow-md"
                    >
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#8cc63f] text-sm font-bold text-[#0f2744]">P</span>
                            <div>
                                <h4 class="font-semibold text-[#0f2744]">Programme assessment</h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    Assess programme design, teaching, staff complement, and quality assurance for one academic programme at a time.
                                </p>
                            </div>
                        </div>
                        <span class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-[#8cc63f] px-4 py-2.5 text-sm font-semibold text-[#0f2744] transition group-hover:bg-[#7ab833]">
                            Start programme assessment
                        </span>
                    </a>
                </div>
            </div>
        @endcan

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-[#0f2744]">Your self-assessments</h3>
                <p class="text-xs text-gray-500 mt-0.5">Open an existing assessment to continue scoring or review results.</p>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left w-12">#</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Type</th>
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
                            <td class="px-4 py-3">{{ ucfirst($assessment->assessment_type) }}</td>
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
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                No self-assessments yet.
                                @can('create', App\Models\Assessment::class)
                                    Use the buttons above to start an institution or programme assessment.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $assessments->links() }}</div>
        </div>
    </div>
</x-app-layout>
