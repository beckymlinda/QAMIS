<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Institutions</h2>
            @can('create', App\Models\Institution::class)
                <a href="{{ route('institutions.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Add Institution</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th>Acronym</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($institutions as $institution)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $institution->name }}</td>
                            <td class="px-4 py-3">{{ $institution->acronym }}</td>
                            <td class="px-4 py-3">{{ $institution->status }}</td>
                            <td class="px-4 py-3"><a href="{{ route('institutions.show', $institution) }}" class="text-indigo-600">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $institutions->links() }}</div>
        </div>
    </div>
</x-app-layout>
