<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Discussions</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ createOpen: false }">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'discussions', 'role' => 'student'])

        <div class="flex justify-end">
            <button type="button" @click="createOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-[#0f2744] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1a3a5c]">
                <i class="bi bi-plus-lg"></i> Start topic
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Topic</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Replies</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Author</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($discussions->sortByDesc('is_pinned') as $discussion)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-[#0f2744]">
                                @if($discussion->is_pinned)<i class="bi bi-pin-angle-fill text-[#8cc63f]"></i> @endif
                                {{ $discussion->title }}
                                @if($discussion->is_closed)
                                    <span class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-600">Closed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $discussion->posts->count() }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $discussion->author?->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <a href="{{ route('student.lms.discussions.show', [$offering, $discussion]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">Open chat</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">No discussion topics yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Start a discussion topic</h3>
                <form method="POST" action="{{ route('student.lms.discussions.store', $offering) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="text" name="title" required placeholder="Topic title" class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="body" rows="4" required placeholder="Opening message" class="w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="createOpen = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Post topic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
