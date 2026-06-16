<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Add Organizational Unit</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('org-units.store') }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required></div>
            <div><label class="block text-sm font-medium">Type</label><select name="type" class="mt-1 w-full rounded-md border-gray-300" required><option value="faculty">Faculty</option><option value="school">School</option><option value="department">Department</option><option value="college">College</option><option value="institute">Institute</option><option value="centre">Centre</option></select></div>
            <div><label class="block text-sm font-medium">Parent Unit</label><select name="parent_id" class="mt-1 w-full rounded-md border-gray-300"><option value="">—</option>@foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
        </form>
    </div>
</x-app-layout>
