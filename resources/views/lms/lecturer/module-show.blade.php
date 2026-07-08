<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Content</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $module->title }}</h2>
            </div>
            <a href="{{ route('lecturer.lms.content', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to content</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ addMaterial: @js($addMaterial ?? false) }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'lecturer'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-5">
                <h3 class="text-lg font-bold text-[#0f2744]">{{ $module->title }}</h3>
                @if($module->description)
                    <p class="mt-2 text-sm text-gray-600">{{ $module->description }}</p>
                @endif
                <p class="mt-2 text-xs text-gray-500">{{ $module->is_published ? 'Published' : 'Draft' }}</p>
            </div>

            <div class="px-6 py-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Materials</p>
                    <button
                        type="button"
                        @click="addMaterial = !addMaterial"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#1a3a5c]"
                    >
                        <i class="bi bi-plus-lg"></i>
                        <span x-text="addMaterial ? 'Hide form' : 'Add material'"></span>
                    </button>
                </div>

                <div class="mt-4 space-y-2">
                    @forelse($module->materials as $material)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 ring-1 ring-gray-100">
                            <span class="text-sm font-medium text-[#0f2744]">{{ $material->title }}</span>
                            <div class="flex gap-3">
                                @if($material->file_path)
                                    <a href="{{ route('lecturer.lms.materials.download', [$offering, $material]) }}" class="text-xs font-semibold text-[#0f2744] hover:text-[#8cc63f]">Download</a>
                                @elseif($material->external_url)
                                    <a href="{{ $material->external_url }}" target="_blank" class="text-xs font-semibold text-[#0f2744] hover:text-[#8cc63f]">Open</a>
                                @endif
                                <form method="POST" action="{{ route('lecturer.lms.materials.destroy', [$offering, $material]) }}" onsubmit="return confirm('Delete this material?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No materials yet. Click <strong>Add material</strong> to upload the first item.</p>
                    @endforelse
                </div>

                <form
                    x-show="addMaterial"
                    x-cloak
                    method="POST"
                    action="{{ route('lecturer.lms.materials.store', [$offering, $module]) }}"
                    enctype="multipart/form-data"
                    class="mt-6 grid gap-3 rounded-xl border border-dashed border-gray-200 p-4 sm:grid-cols-2"
                >
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
                    <div class="flex gap-2 sm:col-span-2">
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-3 py-2 text-xs font-semibold text-[#0f2744]">Save material</button>
                        <button type="button" @click="addMaterial = false" class="rounded-xl px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
