@php
    $portalProfileRoute = auth()->user()->isStudent()
        ? route('student.profile')
        : (auth()->user()->isLecturer() ? route('lecturer.profile') : route('profile.edit'));
@endphp

<div x-data="{ open: false }" class="relative">
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="rounded-full focus:outline-none focus:ring-2 focus:ring-[#8cc63f] focus:ring-offset-2"
        aria-label="Profile menu"
        aria-haspopup="true"
        x-bind:aria-expanded="open.toString()"
    >
        <x-user-avatar :user="auth()->user()" size="md" ring class="shadow-sm transition hover:shadow-md" />
    </button>
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-gray-200"
        role="menu"
    >
        <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3">
            <x-user-avatar :user="auth()->user()" size="sm" />
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-[#0f2744]">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <a href="{{ $portalProfileRoute }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50" role="menuitem">
            <i class="bi bi-person text-gray-400" aria-hidden="true"></i>
            My profile
        </a>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50" role="menuitem">
            <i class="bi bi-camera text-gray-400" aria-hidden="true"></i>
            Photo &amp; account
        </a>
    </div>
</div>
