<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-brand-primary">User management</h2>
                <p class="mt-1 text-sm text-gray-600">Create staff accounts, assign roles, and manage access.</p>
            </div>
            <div class="flex gap-2">
                @can('create', App\Models\User::class)
                    <a href="{{ route('users.create') }}" class="inline-flex rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Add user</a>
                @endcan
                <a href="{{ route('roles.index') }}" class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Manage roles</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="text-xs font-medium text-gray-600">Search</label>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Name or email" class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Role</label>
                <select name="role" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ \App\Support\PermissionCatalog::roleLabel($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            @if($institutions->isNotEmpty())
                <div>
                    <label class="text-xs font-medium text-gray-600">Institution</label>
                    <select name="institution_id" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        <option value="">All institutions</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}" @selected(request('institution_id') == $institution->id)>{{ $institution->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex items-end">
                <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Filter</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        @if(auth()->user()->isNcheOrSystemAdmin())
                            <th class="px-4 py-3">Institution</th>
                        @endif
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Last login</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-800 ring-1 ring-blue-100">{{ \App\Support\PermissionCatalog::roleLabel($role->name) }}</span>
                                @endforeach
                            </td>
                            @if(auth()->user()->isNcheOrSystemAdmin())
                                <td class="px-4 py-3 text-gray-600">{{ $user->institution?->name ?? 'Platform' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                @if($user->is_active)
                                    <span class="text-green-700">Active</span>
                                @else
                                    <span class="text-red-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('update', $user)
                                    <a href="{{ route('users.edit', $user) }}" class="font-medium text-brand-primary">Edit</a>
                                @endcan
                                @can('delete', $user)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user account?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-3 text-red-600">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
