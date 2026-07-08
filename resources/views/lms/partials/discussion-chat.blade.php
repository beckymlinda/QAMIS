@php
    $routePrefix = $role === 'lecturer' ? 'lecturer.lms' : 'student.lms';
    $backRoute = route($routePrefix.'.discussions', $offering);
    $postRoute = route($routePrefix.'.discussions.posts.store', [$offering, $discussion]);
    $updateRoute = route($routePrefix.'.discussions.update', [$offering, $discussion]);
    $closeRoute = route($routePrefix.'.discussions.close', [$offering, $discussion]);
    $destroyRoute = route($routePrefix.'.discussions.destroy', [$offering, $discussion]);
@endphp

<div
    class="flex h-[calc(100vh-12rem)] min-h-[32rem] flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100"
    x-data="{ editOpen: false }"
>
    {{-- Chat header --}}
    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-[#0f2744]/5 to-transparent px-4 py-4 sm:px-5">
        <div class="min-w-0">
            <a href="{{ $backRoute }}" class="text-xs font-semibold text-[#0f2744] hover:text-[#8cc63f]">← Discussions</a>
            <h2 class="mt-1 truncate text-lg font-bold text-[#0f2744]">{{ $discussion->title }}</h2>
            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <span>Started by {{ $discussion->author?->name }}</span>
                @if($discussion->is_pinned)
                    <span class="rounded-full bg-[#8cc63f]/20 px-2 py-0.5 font-semibold text-[#0f2744]">Pinned</span>
                @endif
                @if($discussion->is_closed)
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 font-semibold text-gray-700">Closed</span>
                @endif
            </div>
        </div>

        @if($isCreator)
            <div class="flex shrink-0 flex-wrap justify-end gap-2">
                <button type="button" @click="editOpen = true" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                </button>
                @if($discussion->isOpen())
                    <form method="POST" action="{{ $closeRoute }}" onsubmit="return confirm('Close this discussion? No one will be able to post new messages.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                            <i class="bi bi-lock" aria-hidden="true"></i> Close
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ $destroyRoute }}" onsubmit="return confirm('Delete this discussion and all messages?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                        <i class="bi bi-trash" aria-hidden="true"></i> Delete
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Messages --}}
    <div class="flex-1 space-y-4 overflow-y-auto bg-gray-50/80 px-4 py-4 sm:px-5" id="discussion-messages">
        @foreach($messages as $message)
            @php
                $messageUser = $message['user'];
                $isMine = $messageUser?->id === auth()->id();
            @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="flex max-w-[85%] gap-2 {{ $isMine ? 'flex-row-reverse' : 'flex-row' }}">
                    @if($messageUser)
                        <x-user-avatar :user="$messageUser" size="sm" class="{{ $isMine ? '' : 'ring-2 ring-[#8cc63f]/30' }}" />
                    @else
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-white">?</span>
                    @endif
                    <div>
                        <div class="mb-1 flex items-center gap-2 text-[10px] text-gray-500 {{ $isMine ? 'justify-end' : '' }}">
                            <span class="font-semibold text-gray-600">{{ $messageUser?->name ?? 'Unknown' }}</span>
                            <span>{{ $message['created_at']->format('d M, H:i') }}</span>
                        </div>
                        <div class="rounded-2xl px-4 py-2.5 shadow-sm {{ $isMine ? 'rounded-tr-sm bg-[#0f2744] text-white' : 'rounded-tl-sm bg-white text-gray-800 ring-1 ring-gray-100' }}">
                            @if(filled($message['body']))
                                <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ $message['body'] }}</p>
                            @endif
                            @if(filled($message['file_path']) && $message['post_id'])
                                <a
                                    href="{{ route($routePrefix.'.discussions.posts.file', [$offering, $message['post_id']]) }}"
                                    class="mt-2 inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold {{ $isMine ? 'bg-white/15 text-white hover:bg-white/25' : 'bg-gray-50 text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100' }}"
                                >
                                    <i class="bi bi-paperclip" aria-hidden="true"></i>
                                    {{ $message['file_name'] ?? 'Download attachment' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Composer --}}
    @if($discussion->isOpen())
        <form method="POST" action="{{ $postRoute }}" enctype="multipart/form-data" class="shrink-0 border-t border-gray-100 bg-white px-4 py-3 sm:px-5">
            @csrf
            <div class="flex items-end gap-2">
                <label class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl bg-gray-100 text-[#0f2744] transition hover:bg-gray-200" title="Attach file">
                    <i class="bi bi-paperclip text-lg" aria-hidden="true"></i>
                    <input type="file" name="file" class="sr-only" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                </label>
                <textarea
                    name="body"
                    rows="1"
                    placeholder="Type a message…"
                    class="max-h-32 min-h-[2.5rem] flex-1 resize-y rounded-2xl border-gray-200 text-sm shadow-sm focus:border-[#8cc63f] focus:ring-[#8cc63f]"
                ></textarea>
                <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-[#8cc63f] px-4 text-sm font-semibold text-[#0f2744] hover:bg-[#7ab535]">
                    <i class="bi bi-send-fill" aria-hidden="true"></i>
                    <span class="sr-only">Send</span>
                </button>
            </div>
            <p class="mt-1 text-[10px] text-gray-400">Attach PDF, Word, images, or ZIP · Max 20 MB</p>
        </form>
    @else
        <div class="shrink-0 border-t border-gray-100 bg-gray-50 px-4 py-4 text-center text-sm text-gray-500 sm:px-5">
            <i class="bi bi-lock-fill" aria-hidden="true"></i> This discussion is closed. No new messages can be posted.
        </div>
    @endif

    @if($isCreator)
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-[#0f2744]">Edit discussion</h3>
                <form method="POST" action="{{ $updateRoute }}" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $discussion->title }}" required class="w-full rounded-xl border-gray-300 shadow-sm">
                    <textarea name="body" rows="4" required class="w-full rounded-xl border-gray-300 shadow-sm">{{ $discussion->body }}</textarea>
                    @if($role === 'lecturer')
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_pinned" value="1" @checked($discussion->is_pinned)> Pin topic
                        </label>
                    @endif
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editOpen = false" class="rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const box = document.getElementById('discussion-messages');
        if (box) box.scrollTop = box.scrollHeight;
    });
</script>
