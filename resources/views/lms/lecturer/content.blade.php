<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Learning Content</h2>
            </div>
            <a href="{{ route('lecturer.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ addModule: false, viewModule: null, editModule: null }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'lecturer'])

        <div class="flex justify-end">
            <button type="button" @click="addModule = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1a3a5c]">
                <i class="bi bi-plus-lg"></i> Add module
            </button>
        </div>

        @forelse($modules as $module)
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#8cc63f]/15 text-[#0f2744] text-lg font-bold">{{ $loop->iteration }}</span>
                        <div>
                            <h3 class="text-lg font-bold text-[#0f2744]">{{ $module->title }}</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $module->is_published ? 'Published' : 'Draft' }} · {{ $module->materials->count() }} materials</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="viewModule = @js(['title' => $module->title, 'description' => $module->description, 'materials' => $module->materials->map(fn($m) => ['title' => $m->title, 'type' => \App\Models\LmsMaterial::TYPES[$m->type] ?? $m->type])->values()])" class="rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</button>
                        <button type="button" @click="editModule = @js(['id' => $module->id, 'title' => $module->title, 'description' => $module->description ?? '', 'is_published' => $module->is_published])" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</button>
                        <form method="POST" action="{{ route('lecturer.lms.modules.destroy', [$offering, $module]) }}" onsubmit="return confirm('Delete this module and all materials?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button></form>
                    </div>
                </div>

                <div class="space-y-3 px-6 py-5">
                    @if($module->description)<p class="text-sm text-gray-600">{{ $module->description }}</p>@endif

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Materials</p>
                        @forelse($module->materials as $material)
                            <div class="mt-3 flex items-center justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-gray-100">
                                <span class="text-sm font-medium text-[#0f2744]">{{ $material->title }}</span>
                                <div class="flex gap-2">
                                    @if($material->file_path)
                                        <a href="{{ route('lecturer.lms.materials.download', [$offering, $material]) }}" class="text-xs font-semibold text-[#0f2744] hover:text-[#8cc63f]">Download</a>
                                    @elseif($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" class="text-xs font-semibold text-[#0f2744]">Open</a>
                                    @endif
                                    <form method="POST" action="{{ route('lecturer.lms.materials.destroy', [$offering, $material]) }}">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600">Delete</button></form>
                                </div>
                            </div>
                        @empty
                            <p class="mt-2 text-sm text-gray-500">Nothing added so far.</p>
                        @endforelse

                        <form method="POST" action="{{ route('lecturer.lms.materials.store', [$offering, $module]) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            <input type="text" name="title" placeholder="Material title" required class="rounded-xl border-gray-300 text-sm shadow-sm">
                            <select name="type" class="rounded-xl border-gray-300 text-sm shadow-sm">
                                @foreach(\App\Models\LmsMaterial::TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="file" name="file" class="rounded-xl border-gray-300 text-sm sm:col-span-2">
                            <input type="url" name="external_url" placeholder="External URL (optional)" class="rounded-xl border-gray-300 text-sm">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600"><input type="checkbox" name="allow_download" value="1" checked> Allow download</label>
                            <button type="submit" class="rounded-xl bg-[#8cc63f] px-3 py-2 text-xs font-semibold text-[#0f2744] sm:col-span-2 w-fit">Add material</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
                <i class="bi bi-folder2-open text-4xl text-gray-300"></i>
                <p class="mt-3 text-sm text-gray-500">Nothing added so far. Click <strong>Add module</strong> to begin.</p>
            </div>
        @endforelse

        {{-- Add module modal --}}
        <div x-show="addModule" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Add learning module</h3>
                <form method="POST" action="{{ route('lecturer.lms.modules.store', $offering) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="text" name="title" required placeholder="Module title" class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="description" rows="3" placeholder="Description (optional)" class="w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1"> Publish immediately</label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="addModule = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Save module</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- View module modal --}}
        <div x-show="viewModule" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]" x-text="viewModule?.title"></h3>
                <p class="mt-3 text-sm text-gray-600" x-text="viewModule?.description || 'No description.'"></p>
                <ul class="mt-4 space-y-2 text-sm">
                    <template x-for="material in viewModule?.materials || []" :key="material.title">
                        <li class="rounded-lg bg-gray-50 px-3 py-2" x-text="material.title + ' (' + material.type + ')'"></li>
                    </template>
                </ul>
                <button type="button" @click="viewModule = null" class="mt-6 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium">Close</button>
            </div>
        </div>

        {{-- Edit module modal --}}
        <div x-show="editModule" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Edit module</h3>
                <form x-bind:action="editModule ? '{{ url('lecturer/offerings/'.$offering->id.'/lms/modules') }}/' + editModule.id : '#'" method="POST" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="title" x-model="editModule.title" required class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="description" x-model="editModule.description" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" x-bind:checked="editModule?.is_published"> Published</label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editModule = null" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
