<div class="bg-white rounded-lg shadow p-6" x-data="{ showForm: {{ request('tab') === 'lecturers' && $errors->any() ? 'true' : 'false' }}, editingId: null }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Lecturers</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $lecturers->count() }} academic staff member{{ $lecturers->count() === 1 ? '' : 's' }} assigned to this programme.</p>
        </div>
        <button
            type="button"
            x-show="!showForm"
            @click="showForm = true; editingId = null"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90"
        >Add lecturer</button>
    </div>

    <div x-show="showForm" x-cloak class="mb-6 rounded-lg border border-[#8cc63f]/30 bg-gray-50 p-4">
        <h4 class="text-sm font-semibold text-[#0f2744] mb-1">New lecturer</h4>
        <p class="text-xs text-gray-500 mb-4">Portal login lets them view courses, timetable, and enter grades.</p>

        <form method="POST" action="{{ route('programmes.lecturers.store', $programme) }}" class="grid sm:grid-cols-2 gap-3 text-sm">
            @csrf
            <input type="hidden" name="tab" value="lecturers">
            <div><label class="block text-gray-600 mb-1">Full name</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Email (portal login)</label><input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Designation</label><input name="designation" value="{{ old('designation') }}" placeholder="Lecturer" class="w-full rounded-md border-gray-300"></div>
            <div><label class="block text-gray-600 mb-1">Qualification</label><input name="qualification" value="{{ old('qualification') }}" class="w-full rounded-md border-gray-300"></div>
            <div class="sm:col-span-2 flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="create_portal_login" value="1" checked class="rounded border-gray-300 text-[#8cc63f]"> Create portal login (default password: password)</label>
            </div>
            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-[#0f2744] text-sm font-semibold">Save lecturer</button>
                <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <ul class="divide-y text-sm">
        @forelse($lecturers as $lecturer)
            <li class="py-3" x-show="editingId !== {{ $lecturer->id }}">
                <div class="flex justify-between gap-4">
                    <div>
                        <span class="font-medium text-[#0f2744]">{{ $lecturer->name }}</span>
                        <span class="text-gray-500"> — {{ $lecturer->designation ?? 'Lecturer' }}</span>
                        @if($lecturer->qualification)<p class="text-gray-500 text-xs mt-0.5">{{ $lecturer->qualification }}</p>@endif
                        @if($lecturer->user)<p class="text-gray-500 text-xs mt-0.5">{{ $lecturer->user->email }}</p>@endif
                    </div>
                    <div class="flex shrink-0 items-start gap-3">
                        @if($lecturer->user_id)
                            <span class="text-xs text-green-600">Portal active</span>
                        @else
                            <span class="text-xs text-amber-600" title="Saved to this programme but no portal login is linked yet">No portal login</span>
                        @endif
                        <button type="button" @click="editingId = {{ $lecturer->id }}; showForm = false" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f]">Edit</button>
                    </div>
                </div>
            </li>
            <li class="py-4 bg-gray-50 -mx-6 px-6" x-show="editingId === {{ $lecturer->id }}" x-cloak>
                <form method="POST" action="{{ route('programmes.lecturers.update', [$programme, $lecturer]) }}" class="grid sm:grid-cols-2 gap-3 text-sm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="lecturers">
                    <div><label class="block text-gray-600 mb-1">Full name</label><input name="name" value="{{ $lecturer->name }}" required class="w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-gray-600 mb-1">Email</label><input name="email" type="email" value="{{ $lecturer->user?->email ?? '' }}" class="w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-gray-600 mb-1">Designation</label><input name="designation" value="{{ $lecturer->designation }}" class="w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-gray-600 mb-1">Qualification</label><input name="qualification" value="{{ $lecturer->qualification }}" class="w-full rounded-md border-gray-300"></div>
                    <div class="sm:col-span-2 flex items-end gap-2">
                        <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-white text-sm font-medium">Save changes</button>
                        <button type="button" @click="editingId = null" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </li>
        @empty
            <li class="text-gray-500 py-6 text-center">No lecturers assigned yet. Click <strong>Add lecturer</strong> to add one.</li>
        @endforelse
    </ul>
</div>
