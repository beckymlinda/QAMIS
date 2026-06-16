<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Search</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('search') }}" method="GET" class="mb-6 flex gap-2"><input type="search" name="q" value="{{ $query }}" class="flex-1 rounded-md border-gray-300" placeholder="Search programmes, assessments, evidence, staff..."><button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Search</button></form>
        @if(strlen($query) >= 2)
            <div class="bg-white rounded-lg shadow divide-y">
                @forelse($results as $result)
                    <a href="{{ $result['url'] }}" class="block px-4 py-3 hover:bg-gray-50"><span class="text-xs text-gray-500">{{ $result['type'] }}</span><div>{{ $result['label'] }}</div></a>
                @empty
                    <p class="px-4 py-6 text-gray-500">No results found.</p>
                @endforelse
            </div>
        @endif
    </div>
</x-app-layout>
