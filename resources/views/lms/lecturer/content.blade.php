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

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ addModule: false, editModule: null }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'lecturer'])

        <div class="flex justify-end">
            <button type="button" @click="addModule = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1a3a5c]">
                <i class="bi bi-plus-lg"></i> Add module
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Materials</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($modules as $module)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $module->title }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $module->materials->count() }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $module->is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $module->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('lecturer.lms.modules.show', [$offering, $module]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</a>
                                    <button type="button" @click="editModule = @js(['id' => $module->id, 'title' => $module->title, 'description' => $module->description ?? '', 'is_published' => $module->is_published])" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</button>
                                    <form method="POST" action="{{ route('lecturer.lms.modules.destroy', [$offering, $module]) }}" onsubmit="return confirm('Delete this module and all materials?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">Nothing added yet. Click <strong>Add module</strong> to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="addModule" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Add learning module</h3>
                <form method="POST" action="{{ route('lecturer.lms.modules.store', $offering) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Module</label>
                        <input type="text" name="title" required placeholder="Module title" class="w-full rounded-xl border-gray-300 shadow-sm">
                        <textarea name="description" rows="3" placeholder="Description (optional)" class="mt-3 w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                        <label class="mt-3 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1"> Publish immediately</label>
                    </div>
                    <div class="rounded-xl border border-dashed border-gray-200 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">First material</p>
                        <input type="text" name="material_title" required placeholder="Material title" class="mt-3 w-full rounded-xl border-gray-300 shadow-sm">
                        <select name="material_type" class="mt-3 w-full rounded-xl border-gray-300 text-sm shadow-sm">
                            @foreach(\App\Models\LmsMaterial::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="file" name="material_file" class="mt-3 w-full rounded-xl border-gray-300 text-sm">
                        <input type="url" name="material_external_url" placeholder="External URL (optional)" class="mt-3 w-full rounded-xl border-gray-300 shadow-sm">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="allow_download" value="1" checked> Allow download</label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="addModule = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Save module</button>
                    </div>
                </form>
            </div>
        </div>

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
