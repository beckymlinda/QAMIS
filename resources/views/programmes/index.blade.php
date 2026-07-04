<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Programmes</h2>
            @can('create', App\Models\Programme::class)
                <a href="{{ route('programmes.create') }}" class="px-3 py-1 bg-[#0f2744] text-white rounded-md text-sm hover:bg-[#1a3a5c]">Add Programme</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Level</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programmes as $p)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $p->name }}</td>
                            <td class="px-4 py-3">{{ ucfirst($p->level) }}</td>
                            <td class="px-4 py-3">{{ $p->orgUnit?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $p->nche_accreditation_status ?? '—' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('programmes.show', $p) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">View</a>
                                @can('update', $p)
                                    <span class="text-gray-300 mx-1">|</span>
                                    <a href="{{ route('programmes.academic.index', $p) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">Courses & Students</a>
                                    <span class="text-gray-300 mx-1">|</span>
                                    <a href="{{ route('programmes.edit', $p) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">Edit</a>
                                @endcan
                                @can('delete', $p)
                                    <span class="text-gray-300 mx-1">|</span>
                                    <form method="POST" action="{{ route('programmes.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete this programme?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No programmes registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $programmes->links() }}</div>
        </div>
    </div>
</x-app-layout>
