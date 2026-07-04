<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-[#0f2744]">Institution Assessment</h2>
            <p class="mt-1 text-sm text-gray-600">Assess governance, resources, student support, and other institutional areas using the NCHE institutional accreditation tool.</p>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @can('create', App\Models\Assessment::class)
            <div class="rounded-lg border border-[#0f2744]/15 bg-[#0f2744]/5 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-[#0f2744]">Start institution assessment</h3>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">Evaluate your whole institution against NCHE minimum standards for institutional accreditation.</p>
                <a href="{{ route('assessments.create', ['type' => 'institutional']) }}" class="mt-4 inline-flex rounded-lg bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1a3a5c]">
                    Start institution assessment
                </a>
            </div>
        @endcan

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-[#0f2744]">Institution assessments</h3>
            </div>
            @include('assessments.partials.list', [
                'assessments' => $assessments,
                'emptyMessage' => 'No institution assessments yet.',
            ])
            <div class="p-4">{{ $assessments->links() }}</div>
        </div>
    </div>
</x-app-layout>
