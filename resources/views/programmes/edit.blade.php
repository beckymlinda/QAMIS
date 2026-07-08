<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Edit Programme</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('programmes.update', $programme) }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" value="{{ old('name', $programme->name) }}" class="mt-1 w-full rounded-md border-gray-300" required>
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Department</label>
                <input name="department" value="{{ old('department', $programme->orgUnit?->name) }}" placeholder="e.g. Computer Science" class="mt-1 w-full rounded-md border-gray-300">
                @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Level</label>
                <select name="level" class="mt-1 w-full rounded-md border-gray-300" required>
                    @foreach(['certificate', 'diploma', 'bachelor', 'master', 'doctorate'] as $level)
                        <option value="{{ $level }}" @selected(old('level', $programme->level) === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Accreditation status</label>
                <input name="nche_accreditation_status" value="{{ old('nche_accreditation_status', $programme->nche_accreditation_status) }}" class="mt-1 w-full rounded-md border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium">Delivery Modes</label>
                <div class="mt-2 space-x-4">
                    @foreach(['fulltime', 'parttime', 'distance', 'elearning', 'weekend'] as $mode)
                        <label><input type="checkbox" name="delivery_modes[]" value="{{ $mode }}" @checked(in_array($mode, old('delivery_modes', $programme->delivery_modes ?? [])))> {{ ucfirst($mode) }}</label>
                    @endforeach
                </div>
            </div>
            @include('programmes.partials.admission-fields', ['programme' => $programme])
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-[#0f2744] text-white rounded-md hover:bg-[#1a3a5c]">Save changes</button>
                <a href="{{ route('programmes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
