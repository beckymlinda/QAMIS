<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Reports</h2>
            <div class="flex gap-2">
                @can('generate', App\Models\GeneratedReport::class)
                    <form method="POST" action="{{ route('reports.sar') }}">@csrf<button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Generate SAR</button></form>
                    <form method="POST" action="{{ route('reports.annual') }}">@csrf<button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Generate Annual Report</button></form>
                @endcan
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Type</th><th>Year</th><th>Status</th><th>Generated</th><th></th></tr></thead>
                <tbody>@foreach($reports as $report)<tr class="border-t"><td class="px-4 py-3">{{ $report->template?->name }}</td><td class="px-4 py-3">{{ $report->reporting_year }}</td><td class="px-4 py-3">{{ $report->status }}</td><td class="px-4 py-3">{{ $report->created_at->format('Y-m-d H:i') }}</td><td class="px-4 py-3"><a href="{{ route('reports.show', $report) }}" class="text-indigo-600">View</a></td></tr>@endforeach</tbody>
            </table>
            <div class="p-4">{{ $reports->links() }}</div>
        </div>
    </div>
</x-app-layout>
