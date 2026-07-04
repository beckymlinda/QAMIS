<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'courses' && $errors->any() ? 'true' : 'false' }}, editingId: null }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Courses</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $programme->courses->count() }} course{{ $programme->courses->count() === 1 ? '' : 's' }} in this programme catalogue.</p>
        </div>
        <button
            type="button"
            x-show="!showForm"
            @click="showForm = true; editingId = null"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
        >Add course</button>
    </div>

    <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
        <h4 class="text-sm font-semibold text-[#0f2744] mb-1">Course catalogue</h4>
        <p class="text-xs text-gray-500 mb-4">Define course codes and credit hours before creating semester offerings.</p>

        <form method="POST" action="{{ route('programmes.courses.store', $programme) }}" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
            @csrf
            <input type="hidden" name="tab" value="courses">
            <div><label class="block text-gray-600 mb-1">Course code</label><input name="code" value="{{ old('code') }}" required placeholder="e.g. BUSI-1101" class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Credit hours</label><input name="credit_hours" type="number" step="0.5" value="{{ old('credit_hours', 3) }}" required class="w-full rounded-md border-gray-300"></div>
            <div class="sm:col-span-2 lg:col-span-1"><label class="block text-gray-600 mb-1">Course title</label><input name="title" value="{{ old('title') }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Year level</label><input name="year_level" type="number" min="1" max="8" value="{{ old('year_level') }}" class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Semester</label><input name="semester_number" type="number" min="1" max="3" value="{{ old('semester_number') }}" class="w-full rounded-md border-gray-300"></div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
                <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Save course</button>
                <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-3 py-2 text-left">Code</th>
                <th class="px-3 py-2 text-left">Title</th>
                <th class="px-3 py-2 text-left">CH</th>
                <th class="px-3 py-2 text-left">Year / Sem</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($programme->courses as $course)
                    <tr class="border-t" x-show="editingId !== {{ $course->id }}">
                        <td class="px-3 py-2 font-medium">{{ $course->code }}</td>
                        <td class="px-3 py-2">{{ $course->title }}</td>
                        <td class="px-3 py-2">{{ $course->credit_hours }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $course->year_level ? 'Y'.$course->year_level : '—' }} / {{ $course->semester_number ? 'S'.$course->semester_number : '—' }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <button type="button" @click="editingId = {{ $course->id }}; showForm = false" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f] mr-3">Edit</button>
                            <form method="POST" action="{{ route('programmes.courses.destroy', [$programme, $course]) }}" class="inline" onsubmit="return confirm('Remove this course?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 text-xs font-medium">Remove</button></form>
                        </td>
                    </tr>
                    <tr class="border-t bg-gray-50" x-show="editingId === {{ $course->id }}" x-cloak>
                        <td colspan="5" class="px-3 py-4">
                            <form method="POST" action="{{ route('programmes.courses.update', [$programme, $course]) }}" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="courses">
                                <div><label class="block text-gray-600 mb-1">Course code</label><input name="code" value="{{ $course->code }}" required class="w-full rounded-md border-gray-300"></div>
                                <div><label class="block text-gray-600 mb-1">Credit hours</label><input name="credit_hours" type="number" step="0.5" value="{{ $course->credit_hours }}" required class="w-full rounded-md border-gray-300"></div>
                                <div class="sm:col-span-2 lg:col-span-1"><label class="block text-gray-600 mb-1">Course title</label><input name="title" value="{{ $course->title }}" required class="w-full rounded-md border-gray-300"></div>
                                <div><label class="block text-gray-600 mb-1">Year level</label><input name="year_level" type="number" min="1" max="8" value="{{ $course->year_level }}" class="w-full rounded-md border-gray-300"></div>
                                <div><label class="block text-gray-600 mb-1">Semester</label><input name="semester_number" type="number" min="1" max="3" value="{{ $course->semester_number }}" class="w-full rounded-md border-gray-300"></div>
                                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
                                    <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-white text-sm font-medium">Save changes</button>
                                    <button type="button" @click="editingId = null" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">No courses yet. Click <strong>Add course</strong> to create your first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
