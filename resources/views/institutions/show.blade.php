<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $institution->name }}</h2>
            <div class="flex gap-2">
                @can('update', $institution)
                    <a href="{{ route('institutions.profile.edit', $institution) }}" class="px-3 py-1 bg-gray-200 rounded-md text-sm">Edit Profile</a>
                    <a href="{{ route('institutions.edit', $institution) }}" class="px-3 py-1 bg-gray-200 rounded-md text-sm">Edit</a>
                @endcan
                @if(auth()->user()->isNcheOrSystemAdmin())
                    <form method="POST" action="{{ route('institutions.select') }}">@csrf<input type="hidden" name="institution_id" value="{{ $institution->id }}"><button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Set Active Context</button></form>
                @endif
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Profile</h3>
                <p><strong>Vision:</strong> {{ $institution->profile?->vision ?? '—' }}</p>
                <p class="mt-2"><strong>Mission:</strong> {{ $institution->profile?->mission ?? '—' }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold mb-2">Contact</h3>
                <p>{{ $institution->contact?->email ?? '—' }}</p>
                <p>{{ $institution->contact?->telephone ?? '' }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold mb-2">Programmes ({{ $institution->programmes->count() }})</h3>
            <ul class="list-disc list-inside">@foreach($institution->programmes as $p)<li>{{ $p->name }} ({{ $p->level }})</li>@endforeach</ul>
        </div>
    </div>
</x-app-layout>
