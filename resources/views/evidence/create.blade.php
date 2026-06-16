<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Upload Evidence</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('evidence.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            <div><label class="block text-sm font-medium">Title</label><input name="title" class="mt-1 w-full rounded-md border-gray-300" required></div>
            <div><label class="block text-sm font-medium">Category</label><select name="evidence_category_id" class="mt-1 w-full rounded-md border-gray-300" required>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium">Description</label><textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300"></textarea></div>
            <div><label class="block text-sm font-medium">File</label><input type="file" name="file" class="mt-1 w-full" required></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Upload</button>
        </form>
    </div>
</x-app-layout>
