<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-[#0f2744]">Programme Assessment</h2>
            <p class="mt-1 text-sm text-gray-600">Assess programme design, teaching, staff complement, and quality assurance for each academic programme.</p>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @can('create', App\Models\Assessment::class)
            <div class="rounded-lg border border-[#8cc63f]/40 bg-[#8cc63f]/10 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-[#0f2744]">Start programme assessment</h3>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">Select a programme and score it against the NCHE programme accreditation tool.</p>
                <a href="{{ route('assessments.create', ['type' => 'programme']) }}" class="mt-4 inline-flex rounded-lg bg-[#8cc63f] px-4 py-2.5 text-sm font-semibold text-[#0f2744] hover:bg-[#7ab833]">
                    Start programme assessment
                </a>
            </div>
        @endcan

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-[#0f2744]">Programme assessments</h3>
            </div>
            @include('assessments.partials.list', [
                'assessments' => $assessments,
                'showProgramme' => true,
                'emptyMessage' => 'No programme assessments yet.',
            ])
            <div class="p-4">{{ $assessments->links() }}</div>
        </div>
    </div>
</x-app-layout>
