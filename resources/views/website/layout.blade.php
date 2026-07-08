@php
    $brand = $website->branding();
    $name = $website->displayName();
    $current = $currentPage ?? 'home';
    $homeRoute = !empty($preview) ? route('settings.website.preview', $website) : route('school.home', $website->slug);
    $navItems = [
        'home' => ['label' => 'Home', 'route' => $homeRoute],
        'about' => ['label' => 'About us', 'route' => !empty($preview) ? route('settings.website.preview', $website) : route('school.about', $website->slug)],
        'programs' => ['label' => 'Programs offered', 'route' => !empty($preview) ? route('settings.website.preview', $website) : route('school.programs', $website->slug)],
        'applications' => ['label' => 'Apply online', 'route' => !empty($preview) ? route('settings.website.preview', $website) : route('school.applications', $website->slug)],
        'portal' => ['label' => 'Student portal', 'route' => !empty($preview) ? route('settings.website.preview', $website) : route('school.portal', $website->slug)],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-primary: {{ $brand['primary'] }};
            --brand-secondary: {{ $brand['secondary'] }};
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-800" style="--tw-bg-opacity:1;background-color:#f8fafc;">
    @if(!empty($preview))
        <div class="bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-amber-950">
            Preview mode — this site is not visible to the public until you publish it in Settings.
        </div>
    @endif

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white shadow-sm" x-data="{ mobileOpen: false }">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ $homeRoute }}" class="flex min-w-0 items-center gap-3">
                @if($brand['logo_url'])
                    <img src="{{ $brand['logo_url'] }}" alt="{{ $name }}" class="h-11 w-auto max-w-[160px] object-contain">
                @else
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white" style="background:var(--brand-primary)">{{ strtoupper(substr($name, 0, 1)) }}</span>
                @endif
                <span class="truncate text-lg font-bold" style="color:var(--brand-primary)">{{ $name }}</span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex">
                @foreach($navItems as $key => $item)
                    <a href="{{ $item['route'] }}"
                       class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $current === $key ? '' : 'text-gray-600 hover:bg-gray-100' }}"
                       @if($current === $key) style="background:var(--brand-primary);color:#fff" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" @click="mobileOpen = !mobileOpen" class="inline-flex rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden" aria-label="Toggle menu">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <a href="{{ route('school.portal', $website->slug) }}" class="hidden rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:inline-flex">Student portal</a>
                <a href="{{ route('school.applications', $website->slug) }}" class="hidden rounded-lg px-4 py-2 text-sm font-bold transition hover:opacity-90 sm:inline-flex" style="background:var(--brand-secondary);color:var(--brand-primary)">
                    Apply now
                </a>
            </div>
        </div>

        <div x-show="mobileOpen" x-transition x-cloak class="border-t border-gray-100 bg-white px-4 py-3 lg:hidden">
            <nav class="flex flex-col gap-1">
                @foreach($navItems as $key => $item)
                    <a href="{{ $item['route'] }}"
                       @click="mobileOpen = false"
                       class="rounded-lg px-4 py-2.5 text-sm font-semibold {{ $current === $key ? '' : 'text-gray-700 hover:bg-gray-50' }}"
                       @if($current === $key) style="background:var(--brand-primary);color:#fff" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('school.portal', $website->slug) }}" class="rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Student portal</a>
                <a href="{{ route('school.applications', $website->slug) }}" class="mt-2 rounded-lg px-4 py-2.5 text-center text-sm font-bold" style="background:var(--brand-secondary);color:var(--brand-primary)">
                    Apply now
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-gray-200 text-white" style="background:var(--brand-primary)">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="grid gap-8 md:grid-cols-3">
                <div>
                    <p class="text-lg font-bold">{{ $name }}</p>
                    @if($website->tagline)
                        <p class="mt-2 text-sm text-white/75">{{ $website->tagline }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/60">Contact</p>
                    <ul class="mt-3 space-y-2 text-sm text-white/85">
                        @if($website->footer_address)
                            <li class="flex gap-2"><i class="bi bi-geo-alt shrink-0"></i> {{ $website->footer_address }}</li>
                        @endif
                        @if($website->footer_phone)
                            <li class="flex gap-2"><i class="bi bi-telephone shrink-0"></i> {{ $website->footer_phone }}</li>
                        @endif
                        @if($website->footer_email)
                            <li class="flex gap-2"><i class="bi bi-envelope shrink-0"></i> {{ $website->footer_email }}</li>
                        @endif
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/60">Quick links</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('school.about', $website->slug) }}" class="text-white/85 hover:text-white">About us</a></li>
                        <li><a href="{{ route('school.programs', $website->slug) }}" class="text-white/85 hover:text-white">Programs offered</a></li>
                        <li><a href="{{ route('school.applications', $website->slug) }}" class="text-white/85 hover:text-white">Apply online</a></li>
                        <li><a href="{{ route('school.portal', $website->slug) }}" class="text-white/85 hover:text-white">Student portal</a></li>
                    </ul>
                </div>
            </div>
            @if($website->footer_extra)
                <p class="mt-8 border-t border-white/10 pt-6 text-sm text-white/70 whitespace-pre-wrap">{{ $website->footer_extra }}</p>
            @endif
            <p class="mt-8 text-center text-xs text-white/50">Powered by HEQAMIS</p>
        </div>
    </footer>
</body>
</html>
