<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Assignments</h2>
            </div>
            <a href="{{ route('lecturer.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ createOpen: false, editAssignment: null }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'lecturer'])

        <div class="flex justify-end">
            <button type="button" @click="createOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1a3a5c]">
                <i class="bi bi-plus-lg"></i> Add assignment
            </button>
        </div>

        <div class="space-y-4">
            @forelse($assignments as $assignment)
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#0f2744]">{{ $assignment->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-800"><i class="bi bi-calendar-event"></i> Due date: {{ $assignment->due_at?->format('d M Y H:i') ?? 'No deadline' }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-800"><i class="bi bi-award"></i> Max grade: {{ $assignment->max_score }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-700">{{ $assignment->submissions_count }} submission(s)</span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium {{ $assignment->is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $assignment->is_published ? 'Published' : 'Draft' }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('lecturer.lms.assignments.submissions', [$offering, $assignment]) }}" class="rounded-lg bg-[#0f2744] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#1a3a5c]">Submissions</a>
                            @if($assignment->hasAttachment())
                                <a href="{{ route('lecturer.lms.assignments.attachment', [$offering, $assignment]) }}" class="rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200">PDF</a>
                            @endif
                            <button type="button" @click="editAssignment = @js(['id' => $assignment->id, 'title' => $assignment->title, 'instructions' => $assignment->instructions ?? '', 'due_at' => $assignment->due_at?->format('Y-m-d\TH:i'), 'max_score' => $assignment->max_score, 'allow_late' => $assignment->allow_late, 'is_published' => $assignment->is_published, 'has_attachment' => $assignment->hasAttachment()])" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Edit</button>
                            <form method="POST" action="{{ route('lecturer.lms.assignments.destroy', [$offering, $assignment]) }}" onsubmit="return confirm('Delete this assignment?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Delete</button></form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-500">Nothing added so far.</p>
                </div>
            @endforelse
        </div>

        {{-- Create modal --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Create assignment</h3>
                <form method="POST" action="{{ route('lecturer.lms.assignments.store', $offering) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div><label class="text-sm font-medium text-gray-700">Title</label><input type="text" name="title" required class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="text-sm font-medium text-gray-700">Instructions</label><textarea name="instructions" rows="4" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></textarea></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-sm font-medium text-gray-700">Due date</label><input type="datetime-local" name="due_at" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="text-sm font-medium text-gray-700">Max grade (points)</label><input type="number" name="max_score" value="100" min="1" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                    </div>
                    <div><label class="text-sm font-medium text-gray-700">Assignment PDF (optional)</label><input type="file" name="attachment" accept="application/pdf" class="mt-1 w-full rounded-xl border-gray-300 text-sm"></div>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="allow_late" value="1"> Allow late submission</label>
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" checked> Publish now</label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="createOpen = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Create</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editAssignment" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Edit assignment</h3>
                <form x-bind:action="editAssignment ? '{{ url('lecturer/offerings/'.$offering->id.'/lms/assignments') }}/' + editAssignment.id : '#'" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <div><label class="text-sm font-medium text-gray-700">Title</label><input type="text" name="title" x-model="editAssignment.title" required class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="text-sm font-medium text-gray-700">Instructions</label><textarea name="instructions" x-model="editAssignment.instructions" rows="4" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></textarea></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-sm font-medium text-gray-700">Due date</label><input type="datetime-local" name="due_at" x-model="editAssignment.due_at" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="text-sm font-medium text-gray-700">Max grade (points)</label><input type="number" name="max_score" x-model="editAssignment.max_score" min="1" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></div>
                    </div>
                    <div><label class="text-sm font-medium text-gray-700">Replace PDF</label><input type="file" name="attachment" accept="application/pdf" class="mt-1 w-full rounded-xl border-gray-300 text-sm"><label class="mt-2 inline-flex items-center gap-2 text-xs" x-show="editAssignment?.has_attachment"><input type="checkbox" name="remove_attachment" value="1"> Remove current PDF</label></div>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="allow_late" value="1" x-bind:checked="editAssignment?.allow_late"> Allow late</label>
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" x-bind:checked="editAssignment?.is_published"> Published</label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editAssignment = null" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
