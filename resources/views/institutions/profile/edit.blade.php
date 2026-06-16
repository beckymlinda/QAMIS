<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Institution Profile</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('institutions.profile.update', $institution) }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium">Vision</label><textarea name="vision" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('vision', $profile->vision) }}</textarea></div>
            <div><label class="block text-sm font-medium">Mission</label><textarea name="mission" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('mission', $profile->mission) }}</textarea></div>
            <div><label class="block text-sm font-medium">Core Values</label><textarea name="core_values" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('core_values', $profile->core_values) }}</textarea></div>
            <div><label class="block text-sm font-medium">Strategic Plan Summary</label><textarea name="strategic_plan_summary" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('strategic_plan_summary', $profile->strategic_plan_summary) }}</textarea></div>
            <div><label class="block text-sm font-medium">Background</label><textarea name="background_narrative" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('background_narrative', $profile->background_narrative) }}</textarea></div>
            <h3 class="font-semibold pt-4">SWOT Analysis</h3>
            @foreach (['strengths','weaknesses','opportunities','threats'] as $swot)
                <div><label class="block text-sm font-medium capitalize">{{ $swot }}</label><textarea name="swot_{{ $swot }}" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('swot_'.$swot, $profile->swot_analysis[$swot] ?? '') }}</textarea></div>
            @endforeach
            <h3 class="font-semibold pt-4">Contact</h3>
            <div><label class="block text-sm font-medium">Email</label><input name="email" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('email', $contact->email) }}"></div>
            <div><label class="block text-sm font-medium">Telephone</label><input name="telephone" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('telephone', $contact->telephone) }}"></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save Profile</button>
        </form>
    </div>
</x-app-layout>
