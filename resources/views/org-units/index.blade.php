<x-app-layout>
    <x-slot name="header"><div class="flex justify-between"><h2 class="font-semibold text-xl text-gray-800">Organizational Units</h2><a href="{{ route('org-units.create') }}" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Add Unit</a></div></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th>Type</th><th>Parent</th></tr></thead>
                <tbody>@foreach($orgUnits as $u)<tr class="border-t"><td class="px-4 py-3">{{ $u->name }}</td><td class="px-4 py-3">{{ ucfirst($u->type) }}</td><td class="px-4 py-3">{{ $u->parent?->name ?? '—' }}</td></tr>@endforeach</tbody>
            </table>
            <div class="p-4">{{ $orgUnits->links() }}</div>
        </div>
    </div>
</x-app-layout>
