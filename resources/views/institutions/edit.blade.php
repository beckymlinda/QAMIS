<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Edit Institution</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('institutions.update', $institution) }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('name', $institution->name) }}"></div>
            <div><label class="block text-sm font-medium">Acronym</label><input name="acronym" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('acronym', $institution->acronym) }}"></div>
            <div><label class="block text-sm font-medium">Status</label><select name="status" class="mt-1 w-full rounded-md border-gray-300"><option value="active" @selected($institution->status==='active')>Active</option><option value="inactive" @selected($institution->status==='inactive')>Inactive</option></select></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
        </form>
    </div>
</x-app-layout>
