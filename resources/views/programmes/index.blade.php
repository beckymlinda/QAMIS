<x-app-layout>
    <x-slot name="header"><div class="flex justify-between"><h2 class="font-semibold text-xl text-gray-800">Programmes</h2><a href="{{ route('programmes.create') }}" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Add Programme</a></div></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th>Level</th><th>Department</th><th>Status</th><th></th></tr></thead>
                <tbody>@foreach($programmes as $p)<tr class="border-t"><td class="px-4 py-3">{{ $p->name }}</td><td class="px-4 py-3">{{ $p->level }}</td><td class="px-4 py-3">{{ $p->orgUnit?->name }}</td><td class="px-4 py-3">{{ $p->nche_accreditation_status }}</td><td class="px-4 py-3"><a href="{{ route('programmes.show', $p) }}" class="text-indigo-600">View</a></td></tr>@endforeach</tbody>
            </table>
            <div class="p-4">{{ $programmes->links() }}</div>
        </div>
    </div>
</x-app-layout>
