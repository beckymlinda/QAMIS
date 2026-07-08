<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-brand-primary">Edit role</h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-brand-primary hover:opacity-80">← Back to roles</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                @if($isSystemRole)
                    <p class="text-sm font-medium text-gray-900">{{ \App\Support\PermissionCatalog::roleLabel($role->name) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Built-in role key: {{ $role->name }}</p>
                @else
                    <label class="block text-sm font-medium">Role key *</label>
                    <input name="name" value="{{ old('name', $role->name) }}" required pattern="[a-z][a-z0-9_]*" class="mt-1 w-full rounded-md border-gray-300">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                <h3 class="text-sm font-bold text-brand-primary">Module permissions</h3>
                @include('roles.partials.permission-matrix', compact('modules', 'permissionLabels', 'assignablePermissions', 'selectedPermissions'))
                @error('permissions')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Save role</button>
                <a href="{{ route('roles.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
