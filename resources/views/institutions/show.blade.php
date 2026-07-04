<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800">{{ $institution->name }}</h2>
            <div class="flex gap-2">
                @can('update', $institution)
                    <a href="{{ route('institutions.report-data.index', $institution) }}" class="px-3 py-1 bg-[#8cc63f] text-[#0f2744] rounded-md text-sm font-semibold">Report Data</a>
                    <a href="{{ route('institutions.edit', $institution) }}" class="px-3 py-1 bg-gray-200 rounded-md text-sm">Edit details</a>
                @endcan
                @if(auth()->user()->isNcheOrSystemAdmin())
                    <form method="POST" action="{{ route('institutions.select') }}">@csrf<input type="hidden" name="institution_id" value="{{ $institution->id }}"><button class="px-3 py-1 bg-[#8cc63f] text-[#0f2744] rounded-md text-sm font-medium">Open Institution Workspace</button></form>
                @endif
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')
        @can('update', $institution)
            <div class="bg-[#0f2744]/5 border border-[#0f2744]/20 rounded-lg p-4 text-sm">
                Use <a href="{{ route('institutions.report-data.index', $institution) }}" class="font-semibold text-[#0f2744] underline">Report Data</a> to add executive summary, governance, staff, students, and other SAR sections before generating your report.
            </div>
        @endcan
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Profile</h3>
                <p><strong>Vision:</strong> {{ $institution->profile?->vision ?? '—' }}</p>
                <p class="mt-2"><strong>Mission:</strong> {{ $institution->profile?->mission ?? '—' }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Contact</h3>
                <p>{{ $institution->contact?->email ?? '—' }}</p>
                <p>{{ $institution->contact?->telephone ?? '' }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold mb-2">Programmes ({{ $institution->programmes->count() }})</h3>
            <ul class="list-disc list-inside">@foreach($institution->programmes as $p)<li>{{ $p->name }} ({{ $p->level }})</li>@endforeach</ul>
        </div>
    </div>
</x-app-layout>
