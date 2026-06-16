<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Corrective Actions</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Recommendation</th><th>Assignee</th><th>Deadline</th><th>Status</th><th></th></tr></thead>
                <tbody>@foreach($actions as $action)<tr class="border-t"><td class="px-4 py-3">{{ Str::limit($action->recommendation?->description, 60) }}</td><td class="px-4 py-3">{{ $action->assignee?->name ?? '—' }}</td><td class="px-4 py-3">{{ $action->deadline?->format('Y-m-d') ?? '—' }}</td><td class="px-4 py-3">{{ $action->status }}</td><td class="px-4 py-3">@can('update', $action)<form method="POST" action="{{ route('corrective-actions.update', $action) }}" class="flex gap-1">@csrf @method('PATCH')<select name="status" class="rounded border-gray-300 text-xs"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select><button class="text-indigo-600 text-xs">Save</button></form>@endcan</td></tr>@endforeach</tbody>
            </table>
            <div class="p-4">{{ $actions->links() }}</div>
        </div>
    </div>
</x-app-layout>
