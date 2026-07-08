<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-brand-primary">Roles & permissions</h2>
                <p class="mt-1 text-sm text-gray-600">Built-in roles from HEQAMIS modules, plus custom roles you create.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('users.index') }}" class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Users</a>
                <a href="{{ route('roles.create') }}" class="inline-flex rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Add role</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Permissions</th>
                        <th class="px-4 py-3">Users</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ \App\Support\PermissionCatalog::roleLabel($role->name) }}</p>
                                <p class="text-xs text-gray-500">{{ $role->name }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if(\App\Support\PermissionCatalog::isSystemRole($role))
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Built-in</span>
                                @else
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">Custom</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $role->permissions->count() }} module permission(s)</td>
                            <td class="px-4 py-3">{{ $role->users_count }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if(\App\Support\PermissionCatalog::canManageRole(auth()->user(), $role) || (auth()->user()->hasRole('system_admin') && \App\Support\PermissionCatalog::isSystemRole($role)))
                                    <a href="{{ route('roles.edit', $role) }}" class="font-medium text-brand-primary">Edit</a>
                                @else
                                    <span class="text-xs text-gray-400">View only</span>
                                @endif
                                @if(\App\Support\PermissionCatalog::canManageRole(auth()->user(), $role) && ! \App\Support\PermissionCatalog::isSystemRole($role))
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete this role?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-3 text-red-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
