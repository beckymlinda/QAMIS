<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-brand-primary">Edit user</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-brand-primary hover:opacity-80">← Back to users</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="POST" action="{{ route('users.update', $managedUser) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            @csrf @method('PUT')
            @include('users.partials.form-fields', compact('roles', 'institutions', 'defaultInstitutionId', 'selectedRole', 'managedUser'))
            <div class="mt-6 flex gap-3 border-t border-gray-100 pt-4">
                <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Save changes</button>
                <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
