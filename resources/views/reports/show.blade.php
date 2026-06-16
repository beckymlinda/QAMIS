<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ $report->template?->name }}</h2></x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-6 rounded-lg shadow flex gap-4">
            @if($report->file_pdf_path)<a href="{{ route('reports.download', [$report, 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-md">Download PDF</a>@endif
            @if($report->file_docx_path)<a href="{{ route('reports.download', [$report, 'docx']) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md">Download Word</a>@endif
        </div>
        <div class="bg-white p-6 rounded-lg shadow"><h3 class="font-semibold mb-2">Report Snapshot</h3><pre class="text-xs overflow-auto max-h-96 bg-gray-50 p-4 rounded">{{ json_encode($report->snapshot_data, JSON_PRETTY_PRINT) }}</pre></div>
    </div>
</x-app-layout>
