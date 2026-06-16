<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Create Institution</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        <form method="POST" action="{{ route('institutions.store') }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('name') }}"></div>
            <div><label class="block text-sm font-medium">Acronym</label><input name="acronym" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('acronym') }}"></div>
            <div><label class="block text-sm font-medium">Establishment Year</label><input type="number" name="establishment_year" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('establishment_year') }}"></div>
            <div><label class="block text-sm font-medium">Website</label><input type="url" name="web_address" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('web_address') }}"></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
        </form>
    </div>
</x-app-layout>
