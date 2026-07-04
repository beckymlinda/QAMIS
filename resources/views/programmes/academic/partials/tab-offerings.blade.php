@php
    $hasOfferings = false;
    foreach ($programme->courses as $course) {
        foreach ($course->offerings as $offering) {
            $hasOfferings = true;
            break 2;
        }
    }
@endphp

<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'offerings' && $errors->any() ? 'true' : 'false' }}, editingId: null }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Semester offerings</h3>
            <p class="text-sm text-gray-500 mt-1">Assign lecturers and semesters to courses. Enrol students for the student portal.</p>
        </div>
        @if($programme->courses->isNotEmpty())
            <button
                type="button"
                x-show="!showForm"
                @click="showForm = true; editingId = null"
                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
            >Add offering</button>
        @endif
    </div>

    @if($programme->courses->isEmpty())
        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-4">Add courses in the <strong>Courses</strong> tab before creating offerings.</p>
    @else
        <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
            <h4 class="text-sm font-semibold text-[#0f2744] mb-1">New semester offering</h4>
            <p class="text-xs text-gray-500 mb-4">Link a course to a lecturer and academic period.</p>

            <form method="POST" action="{{ route('programmes.offerings.store', $programme) }}" class="grid md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm items-end">
                @csrf
                <input type="hidden" name="tab" value="offerings">
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-gray-600 mb-1">Course</label>
                    <select name="course_id" required class="w-full rounded-md border-gray-300">
                        @foreach($programme->courses as $course)
                            <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Lecturer</label>
                    <select name="staff_member_id" class="w-full rounded-md border-gray-300">
                        <option value="">— Select —</option>
                        @foreach($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}">{{ $lecturer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-gray-600 mb-1">Academic year</label><input name="academic_year" value="{{ old('academic_year', $currentYear) }}" required class="w-full rounded-md border-gray-300"></div>
                <div><label class="block text-gray-600 mb-1">Semester</label><input name="semester" type="number" min="1" max="3" value="{{ old('semester', 1) }}" required class="w-full rounded-md border-gray-300"></div>
                <div>
                    <label class="block text-gray-600 mb-1">Delivery</label>
                    <select name="delivery_mode" class="w-full rounded-md border-gray-300">
                        <option value="face_to_face">Face-to-face</option>
                        <option value="online">Online</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2"><input type="checkbox" name="enrol_all_students" value="1" checked class="rounded border-gray-300 text-[#8cc63f]"> Enrol all active students</label>
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Create offering</button>
                    <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-3 py-2 text-left">Course</th>
                    <th class="px-3 py-2 text-left">Lecturer</th>
                    <th class="px-3 py-2 text-left">Year / Sem</th>
                    <th class="px-3 py-2 text-left">Enrolled</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($programme->courses as $course)
                        @foreach($course->offerings as $offering)
                            <tr class="border-t" x-show="editingId !== {{ $offering->id }}">
                                <td class="px-3 py-2 font-medium">{{ $course->code }}<br><span class="text-gray-500 font-normal">{{ $course->title }}</span></td>
                                <td class="px-3 py-2">{{ $offering->lecturer?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $offering->academic_year }} / Sem {{ $offering->semester }}</td>
                                <td class="px-3 py-2">{{ $offering->studentEnrolments->count() }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <button type="button" @click="editingId = {{ $offering->id }}; showForm = false" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f] mr-3">Edit</button>
                                    <form method="POST" action="{{ route('programmes.offerings.destroy', [$programme, $offering]) }}" class="inline" onsubmit="return confirm('Remove offering?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 text-xs font-medium">Remove</button></form>
                                </td>
                            </tr>
                            <tr class="border-t bg-gray-50" x-show="editingId === {{ $offering->id }}" x-cloak>
                                <td colspan="5" class="px-3 py-4">
                                    <form method="POST" action="{{ route('programmes.offerings.update', [$programme, $offering]) }}" class="grid md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm items-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="tab" value="offerings">
                                        <div>
                                            <label class="block text-gray-600 mb-1">Lecturer</label>
                                            <select name="staff_member_id" class="w-full rounded-md border-gray-300">
                                                <option value="">— Select —</option>
                                                @foreach($lecturers as $lecturer)
                                                    <option value="{{ $lecturer->id }}" @selected($offering->staff_member_id === $lecturer->id)>{{ $lecturer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div><label class="block text-gray-600 mb-1">Academic year</label><input name="academic_year" value="{{ $offering->academic_year }}" required class="w-full rounded-md border-gray-300"></div>
                                        <div><label class="block text-gray-600 mb-1">Semester</label><input name="semester" type="number" min="1" max="3" value="{{ $offering->semester }}" required class="w-full rounded-md border-gray-300"></div>
                                        <div>
                                            <label class="block text-gray-600 mb-1">Delivery</label>
                                            <select name="delivery_mode" class="w-full rounded-md border-gray-300">
                                                <option value="face_to_face" @selected($offering->delivery_mode === 'face_to_face')>Face-to-face</option>
                                                <option value="online" @selected($offering->delivery_mode === 'online')>Online</option>
                                                <option value="hybrid" @selected($offering->delivery_mode === 'hybrid')>Hybrid</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 lg:col-span-3 flex items-end gap-2">
                                            <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-white text-sm font-medium">Save changes</button>
                                            <button type="button" @click="editingId = null" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    @unless($hasOfferings)
                        <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">No offerings yet. Click <strong>Add offering</strong> to create one.</td></tr>
                    @endunless
                </tbody>
            </table>
        </div>
    @endif
</div>
