<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'venues' && $errors->any() ? 'true' : 'false' }}, editingId: null }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Classrooms & venues</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $classrooms->count() }} venue{{ $classrooms->count() === 1 ? '' : 's' }} available for timetabling.</p>
        </div>
        <button
            type="button"
            x-show="!showForm"
            @click="showForm = true; editingId = null"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
        >Add venue</button>
    </div>

    <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
        <h4 class="text-sm font-semibold text-[#0f2744] mb-1">New venue</h4>
        <p class="text-xs text-gray-500 mb-4">Rooms used when building the timetable.</p>

        <form method="POST" action="{{ route('programmes.classrooms.store', $programme) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm items-end">
            @csrf
            <input type="hidden" name="tab" value="venues">
            <div><label class="block text-gray-600 mb-1">Code</label><input name="code" value="{{ old('code') }}" required placeholder="LR-101" class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Name</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Capacity</label><input name="capacity" type="number" value="{{ old('capacity', 40) }}" required class="w-full rounded-md border-gray-300"></div>
            <div>
                <label class="block text-gray-600 mb-1">Type</label>
                <select name="room_type" class="w-full rounded-md border-gray-300">
                    <option value="lecture">Lecture room</option>
                    <option value="laboratory">Laboratory</option>
                    <option value="computer">Computer room</option>
                    <option value="seminar">Seminar room</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4 flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Save venue</button>
                <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <ul class="divide-y text-sm">
        @forelse($classrooms as $room)
            <li class="py-3" x-show="editingId !== {{ $room->id }}">
                <div class="flex justify-between gap-4">
                    <span><strong class="text-[#0f2744]">{{ $room->code }}</strong> — {{ $room->name }}</span>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="text-gray-500">{{ $room->capacity }} seats · {{ ucfirst($room->room_type) }}</span>
                        <button type="button" @click="editingId = {{ $room->id }}; showForm = false" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f]">Edit</button>
                    </div>
                </div>
            </li>
            <li class="py-4 bg-gray-50 -mx-6 px-6" x-show="editingId === {{ $room->id }}" x-cloak>
                <form method="POST" action="{{ route('programmes.classrooms.update', [$programme, $room]) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm items-end">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="venues">
                    <div><label class="block text-gray-600 mb-1">Code</label><input name="code" value="{{ $room->code }}" required class="w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-gray-600 mb-1">Name</label><input name="name" value="{{ $room->name }}" required class="w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-gray-600 mb-1">Capacity</label><input name="capacity" type="number" value="{{ $room->capacity }}" required class="w-full rounded-md border-gray-300"></div>
                    <div>
                        <label class="block text-gray-600 mb-1">Type</label>
                        <select name="room_type" class="w-full rounded-md border-gray-300">
                            <option value="lecture" @selected($room->room_type === 'lecture')>Lecture room</option>
                            <option value="laboratory" @selected($room->room_type === 'laboratory')>Laboratory</option>
                            <option value="computer" @selected($room->room_type === 'computer')>Computer room</option>
                            <option value="seminar" @selected($room->room_type === 'seminar')>Seminar room</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4 flex items-end gap-2">
                        <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-white text-sm font-medium">Save changes</button>
                        <button type="button" @click="editingId = null" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </li>
        @empty
            <li class="text-gray-500 py-6 text-center">No venues defined yet. Click <strong>Add venue</strong> to add one.</li>
        @endforelse
    </ul>
</div>
