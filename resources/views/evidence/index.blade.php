<x-app-layout>
    <x-slot name="header"><div class="flex justify-between"><h2 class="font-semibold text-xl text-gray-800">Evidence Repository</h2><a href="{{ route('evidence.create') }}" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Upload</a></div></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Title</th><th>Category</th><th>Version</th><th>Uploaded</th></tr></thead>
                <tbody>@foreach($documents as $doc)<tr class="border-t"><td class="px-4 py-3">{{ $doc->title }}</td><td class="px-4 py-3">{{ $doc->category?->name }}</td><td class="px-4 py-3">v{{ $doc->currentVersion?->version_no ?? 1 }}</td><td class="px-4 py-3">{{ $doc->currentVersion?->uploaded_at?->format('Y-m-d') }}</td></tr>@endforeach</tbody>
            </table>
            <div class="p-4">{{ $documents->links() }}</div>
        </div>
    </div>
</x-app-layout>
