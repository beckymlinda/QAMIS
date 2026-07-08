<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Announcements</h2>
            </div>
            <a href="{{ route('lecturer.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ createOpen: false, editAnnouncement: null }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'announcements', 'role' => 'lecturer'])

        <div class="flex justify-end">
            <button type="button" @click="createOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1a3a5c]">
                <i class="bi bi-plus-lg"></i> Add announcement
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $announcement->title }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $announcement->isPublished() ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-800' }}">
                                    {{ $announcement->isPublished() ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $announcement->published_at?->format('d M Y H:i') ?? $announcement->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('lecturer.lms.announcements.show', [$offering, $announcement]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</a>
                                    <button type="button" @click="editAnnouncement = @js(['id' => $announcement->id, 'title' => $announcement->title, 'body' => $announcement->body, 'is_published' => $announcement->isPublished()])" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</button>
                                    <form method="POST" action="{{ route('lecturer.lms.announcements.destroy', [$offering, $announcement]) }}" onsubmit="return confirm('Delete this announcement?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">Nothing added yet. Click <strong>Add announcement</strong> to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Add announcement</h3>
                <form method="POST" action="{{ route('lecturer.lms.announcements.store', $offering) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="text" name="title" required placeholder="Title" class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="body" rows="5" required placeholder="Message to students" class="w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="publish_now" value="1" checked> Publish and notify students</label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="createOpen = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="editAnnouncement" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Edit announcement</h3>
                <form x-bind:action="editAnnouncement ? '{{ url('lecturer/offerings/'.$offering->id.'/lms/announcements') }}/' + editAnnouncement.id : '#'" method="POST" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="title" x-model="editAnnouncement.title" required class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="body" x-model="editAnnouncement.body" rows="5" required class="w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="publish_now" value="1" x-bind:checked="editAnnouncement?.is_published"> Published</label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editAnnouncement = null" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
