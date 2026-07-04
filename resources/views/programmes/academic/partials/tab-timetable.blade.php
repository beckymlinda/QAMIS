@php
    $hasOfferings = $programme->courses->contains(fn ($c) => $c->offerings->isNotEmpty());
    $hasSlots = false;
    if ($hasOfferings) {
        foreach ($programme->courses as $course) {
            foreach ($course->offerings as $offering) {
                if ($offering->timetableSlots->isNotEmpty()) {
                    $hasSlots = true;
                    break 2;
                }
            }
        }
    }
@endphp

<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'timetable' && $errors->hasAny(['course_offering_id', 'day_of_week', 'start_time', 'end_time', 'classroom_id']) ? 'true' : 'false' }}, showAutoForm: {{ request('tab') === 'timetable' && ($errors->has('day_start') || $errors->has('day_end')) ? 'true' : 'false' }}, editingId: null }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Timetable</h3>
            <p class="text-sm text-gray-500 mt-1">Class sessions visible to students and lecturers. Conflicts for rooms, lecturers, and lunch (12:00–13:00) are blocked.</p>
        </div>
        @if($hasOfferings)
            <div class="flex flex-wrap gap-2 shrink-0">
                <button
                    type="button"
                    x-show="!showForm && !showAutoForm"
                    @click="showAutoForm = true; showForm = false; editingId = null"
                    class="inline-flex items-center justify-center rounded-lg border border-[#8cc63f] bg-[#8cc63f]/15 px-4 py-2 text-sm font-semibold text-[#0f2744] hover:bg-[#8cc63f]/25"
                >{{ $hasSlots ? 'Regenerate' : 'Auto-generate' }}</button>
                <button
                    type="button"
                    x-show="!showForm && !showAutoForm"
                    @click="showForm = true; showAutoForm = false; editingId = null"
                    class="inline-flex items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
                >Add slot</button>
            </div>
        @endif
    </div>

    @unless($hasOfferings)
        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-4">Create semester offerings in the <strong>Offerings</strong> tab before adding timetable slots.</p>
    @else
        <div x-show="showAutoForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/40 bg-[#8cc63f]/5 p-4">
            <h4 class="text-sm font-semibold text-[#0f2744] mb-1">{{ $hasSlots ? 'Regenerate timetable' : 'Auto-generate timetable' }}</h4>
            <p class="text-xs text-gray-500 mb-4">
                @if($hasSlots)
                    Rebuilds the timetable with a new day, time, and room allocation across Monday–Friday. Lunch stays free from 12:00 to 13:00.
                @else
                    Builds one 2-hour session per course offering using available rooms and lecturers. Lunch is always kept free from 12:00 to 13:00.
                @endif
            </p>

            <form method="POST" action="{{ route('programmes.timetable.auto-generate', $programme) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm items-end" onsubmit="return confirm(@js($hasSlots ? 'Replace the current timetable with a new allocation across the week?' : 'This will create timetable slots for all course offerings. Continue?'));">
                @csrf
                <input type="hidden" name="tab" value="timetable">
                <input type="hidden" name="replace_existing" value="1">
                <div>
                    <label class="block text-gray-600 mb-1">Day starts at</label>
                    <input name="day_start" type="time" value="{{ old('day_start', '08:00') }}" required class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Day ends at</label>
                    <input name="day_end" type="time" value="{{ old('day_end', '17:00') }}" required class="w-full rounded-md border-gray-300">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 rounded-md bg-white/80 border border-gray-200 px-3 py-2 text-xs text-gray-600">
                    Uses {{ $classrooms->count() }} venue{{ $classrooms->count() === 1 ? '' : 's' }} and {{ $programme->courses->sum(fn ($c) => $c->offerings->count()) }} offering{{ $programme->courses->sum(fn ($c) => $c->offerings->count()) === 1 ? '' : 's' }}. Sessions are spread across Monday–Friday.
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">{{ $hasSlots ? 'Regenerate timetable' : 'Generate timetable' }}</button>
                    <button type="button" @click="showAutoForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>

        <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
            <h4 class="text-sm font-semibold text-[#0f2744] mb-1">New timetable slot</h4>
            <p class="text-xs text-gray-500 mb-4">Schedule a session for a course offering. Overlapping room or lecturer bookings are not allowed.</p>

            <form method="POST" action="{{ route('programmes.timetable-slots.store', $programme) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm items-end">
                @csrf
                <input type="hidden" name="tab" value="timetable">
                <div class="sm:col-span-2">
                    <label class="block text-gray-600 mb-1">Course offering</label>
                    <select name="course_offering_id" required class="w-full rounded-md border-gray-300">
                        @foreach($programme->courses as $course)
                            @foreach($course->offerings as $offering)
                                <option value="{{ $offering->id }}">{{ $course->code }} — {{ $offering->academic_year }} Sem {{ $offering->semester }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Day</label>
                    <select name="day_of_week" required class="w-full rounded-md border-gray-300">
                        @foreach(\App\Models\TimetableSlot::dayNames() as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-gray-600 mb-1">Start</label><input name="start_time" type="time" value="{{ old('start_time') }}" required class="w-full rounded-md border-gray-300"></div>
                <div><label class="block text-gray-600 mb-1">End</label><input name="end_time" type="time" value="{{ old('end_time') }}" required class="w-full rounded-md border-gray-300"></div>
                <div>
                    <label class="block text-gray-600 mb-1">Session type</label>
                    <select name="session_type" class="w-full rounded-md border-gray-300">
                        <option value="lecture">Lecture</option>
                        <option value="laboratory">Laboratory</option>
                        <option value="tutorial">Tutorial</option>
                        <option value="seminar">Seminar</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Room</label>
                    <select name="classroom_id" class="w-full rounded-md border-gray-300">
                        <option value="">— Other —</option>
                        @foreach($classrooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-gray-600 mb-1">Other venue</label><input name="venue_name" placeholder="If not in list" class="w-full rounded-md border-gray-300"></div>
                <div class="sm:col-span-2 lg:col-span-4 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Add slot</button>
                    <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-3 py-2 text-left">Course</th>
                    <th class="px-3 py-2 text-left">Lecturer</th>
                    <th class="px-3 py-2 text-left">Day / Time</th>
                    <th class="px-3 py-2 text-left">Venue</th>
                    <th class="px-3 py-2 text-left">Type</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($programme->courses as $course)
                        @foreach($course->offerings as $offering)
                            @foreach($offering->timetableSlots as $slot)
                                <tr class="border-t" x-show="editingId !== {{ $slot->id }}">
                                    <td class="px-3 py-2 font-medium">{{ $course->code }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $offering->lecturer?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $slot->dayName() }}, {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</td>
                                    <td class="px-3 py-2">{{ $slot->venueLabel() }}</td>
                                    <td class="px-3 py-2 capitalize">{{ $slot->session_type }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        <button type="button" @click="editingId = {{ $slot->id }}; showForm = false; showAutoForm = false" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f] mr-3">Edit</button>
                                        <form method="POST" action="{{ route('programmes.timetable-slots.destroy', [$programme, $slot]) }}" class="inline" onsubmit="return confirm('Remove slot?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 text-xs font-medium">Remove</button></form>
                                    </td>
                                </tr>
                                <tr class="border-t bg-gray-50" x-show="editingId === {{ $slot->id }}" x-cloak>
                                    <td colspan="6" class="px-3 py-4">
                                        <form method="POST" action="{{ route('programmes.timetable-slots.update', [$programme, $slot]) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm items-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="tab" value="timetable">
                                            <div>
                                                <label class="block text-gray-600 mb-1">Day</label>
                                                <select name="day_of_week" required class="w-full rounded-md border-gray-300">
                                                    @foreach(\App\Models\TimetableSlot::dayNames() as $num => $name)
                                                        <option value="{{ $num }}" @selected($slot->day_of_week == $num)>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div><label class="block text-gray-600 mb-1">Start</label><input name="start_time" type="time" value="{{ substr($slot->start_time, 0, 5) }}" required class="w-full rounded-md border-gray-300"></div>
                                            <div><label class="block text-gray-600 mb-1">End</label><input name="end_time" type="time" value="{{ substr($slot->end_time, 0, 5) }}" required class="w-full rounded-md border-gray-300"></div>
                                            <div>
                                                <label class="block text-gray-600 mb-1">Session type</label>
                                                <select name="session_type" class="w-full rounded-md border-gray-300">
                                                    <option value="lecture" @selected($slot->session_type === 'lecture')>Lecture</option>
                                                    <option value="laboratory" @selected($slot->session_type === 'laboratory')>Laboratory</option>
                                                    <option value="tutorial" @selected($slot->session_type === 'tutorial')>Tutorial</option>
                                                    <option value="seminar" @selected($slot->session_type === 'seminar')>Seminar</option>
                                                    <option value="online" @selected($slot->session_type === 'online')>Online</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-gray-600 mb-1">Room</label>
                                                <select name="classroom_id" class="w-full rounded-md border-gray-300">
                                                    <option value="">— Other —</option>
                                                    @foreach($classrooms as $room)
                                                        <option value="{{ $room->id }}" @selected($slot->classroom_id === $room->id)>{{ $room->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div><label class="block text-gray-600 mb-1">Other venue</label><input name="venue_name" value="{{ $slot->venue_name }}" placeholder="If not in list" class="w-full rounded-md border-gray-300"></div>
                                            <div class="sm:col-span-2 lg:col-span-4 flex items-end gap-2">
                                                <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-white text-sm font-medium">Save changes</button>
                                                <button type="button" @click="editingId = null" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                    @unless($hasSlots)
                        <tr><td colspan="6" class="px-3 py-8 text-center text-gray-500">No timetable slots yet. Use <strong>Auto-generate</strong> or <strong>Add slot</strong>.</td></tr>
                    @endunless
                </tbody>
            </table>
        </div>
    @endunless
</div>
