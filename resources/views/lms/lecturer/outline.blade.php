<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Course Outline</h2>
            </div>
            <a href="{{ route('lecturer.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ addOpen: false, addType: 'learning_outcome', viewItem: null, editItem: null }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'outline', 'role' => 'lecturer'])

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600">Add typed content or upload documents for each outline section.</p>
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3a5c]">
                    <i class="bi bi-plus-lg"></i> Add
                    <i class="bi bi-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-gray-200">
                    @foreach(\App\Models\LmsOutlineItem::TYPES as $type => $label)
                        <button type="button" @click="addType = @js($type); addOpen = true; open = false" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-file-earmark-text text-[#8cc63f]"></i> {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        @foreach(\App\Models\LmsOutlineItem::TYPES as $type => $label)
            @php $items = $groupedItems->get($type, collect()); @endphp
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0f2744]/10 text-[#0f2744]"><i class="bi bi-journal-text"></i></span>
                        <div>
                            <h3 class="font-semibold text-[#0f2744]">{{ $label }}</h3>
                            <p class="text-xs text-gray-500">{{ $items->count() }} item(s)</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-medium text-[#0f2744]">{{ $item->title }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    @if($item->hasDocument())
                                        <i class="bi bi-paperclip"></i> Document attached
                                    @else
                                        <i class="bi bi-text-left"></i> Typed content
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="viewItem = @js(['title' => $item->title, 'body' => $item->body, 'has_file' => $item->hasDocument(), 'download' => $item->hasDocument() ? route('lecturer.lms.outline.items.download', [$offering, $item]) : null])" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</button>
                                <button type="button" @click="editItem = @js(['id' => $item->id, 'title' => $item->title, 'body' => $item->body, 'has_file' => $item->hasDocument()])" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</button>
                                <form method="POST" action="{{ route('lecturer.lms.outline.items.destroy', [$offering, $item]) }}" onsubmit="return confirm('Delete this item?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-10 text-center text-sm text-gray-500">Nothing added so far.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        {{-- Add modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div @click.outside="addOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Add outline item</h3>
                <form method="POST" action="{{ route('lecturer.lms.outline.items.store', $offering) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" x-model="addType">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" required class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type content (optional if uploading)</label>
                        <textarea name="body" rows="5" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]" placeholder="Type your content here..."></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Or upload document</label>
                        <input type="file" name="file" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="addOpen = false" class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- View modal --}}
        <div x-show="viewItem" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]" x-text="viewItem?.title"></h3>
                <div class="mt-4 max-h-[60vh] overflow-y-auto text-sm whitespace-pre-wrap text-gray-700" x-text="viewItem?.body || 'No typed content.'"></div>
                <template x-if="viewItem?.download">
                    <a :href="viewItem.download" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#0f2744] hover:text-[#8cc63f]"><i class="bi bi-download"></i> Download document</a>
                </template>
                <button type="button" @click="viewItem = null" class="mt-6 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium">Close</button>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editItem" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Edit outline item</h3>
                <form :action="editItem ? '{{ url('lecturer/offerings/'.$offering->id.'/lms/outline/items') }}/' + editItem.id : '#'" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" x-model="editItem.title" required class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Content</label>
                        <textarea name="body" rows="5" x-model="editItem.body" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Replace document</label>
                        <input type="file" name="file" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600" x-show="editItem?.has_file"><input type="checkbox" name="remove_file" value="1"> Remove current document</label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editItem = null" class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
