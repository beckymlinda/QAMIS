<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.full_name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-heqamis-blue text-white min-h-screen flex flex-col">
        <header class="border-b border-heqamis-blue-light/60">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    @if (file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.short_name') }}" class="h-12 w-auto object-contain">
                    @else
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-heqamis-green text-sm font-bold text-heqamis-blue">E</span>
                    @endif
                    <span class="text-xl font-bold tracking-wide">{{ config('app.short_name') }}</span>
                </a>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-heqamis-green px-5 py-2 text-sm font-semibold text-heqamis-blue hover:bg-heqamis-green-dark transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg border border-white/30 px-5 py-2 text-sm font-medium hover:bg-white/10 transition">
                            Log in
                        </a>
                        @if (Route::has('register.guest'))
                            <a href="{{ route('register.guest') }}" class="rounded-lg bg-heqamis-green px-5 py-2 text-sm font-semibold text-heqamis-blue hover:bg-heqamis-green-dark transition">
                                Guest institution
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="mb-4 inline-block rounded-full bg-heqamis-green/20 px-4 py-1 text-sm font-medium text-heqamis-green">
                            NCHE Accreditation Support
                        </p>
                        <h1 class="text-4xl font-bold leading-tight lg:text-5xl">
                            Welcome to {{ config('app.short_name') }}
                        </h1>
                        <p class="mt-6 text-lg text-white/85 leading-relaxed">
                            {{ config('app.full_name') }} helps institutions and programmes conduct institutional and programme
                            self-assessments against NCHE standards, track compliance, manage evidence, and generate reports.
                        </p>
                        <ul class="mt-8 space-y-3 text-white/80">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-heqamis-green text-xs font-bold text-heqamis-blue">✓</span>
                                Rubric-based scoring for institutional and programme areas
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-heqamis-green text-xs font-bold text-heqamis-blue">✓</span>
                                Evidence management and gap analysis guidance
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-heqamis-green text-xs font-bold text-heqamis-blue">✓</span>
                                SAR and annual report generation in PDF and DOCX
                            </li>
                        </ul>

                        @guest
                            <div class="mt-10 flex flex-wrap gap-4">
                                <a href="{{ route('login') }}" class="rounded-lg bg-heqamis-green px-8 py-3 text-base font-semibold text-heqamis-blue shadow-lg hover:bg-heqamis-green-dark transition">
                                    Log in
                                </a>
                                @if (Route::has('register.guest'))
                                    <a href="{{ route('register.guest') }}" class="rounded-lg border border-heqamis-green px-8 py-3 text-base font-semibold text-heqamis-green hover:bg-heqamis-green/10 transition">
                                        Register as guest institution
                                    </a>
                                @endif
                            </div>
                        @endguest
                    </div>

                    <div class="relative">
                        <div class="rounded-2xl border border-heqamis-blue-light bg-heqamis-blue-light/40 p-8 shadow-2xl backdrop-blur-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-heqamis-green text-2xl font-bold text-heqamis-blue">E</span>
                                <div>
                                    <p class="text-sm uppercase tracking-wider text-heqamis-green">Platform</p>
                                    <p class="text-xl font-semibold">Self-Assessment Workflow</p>
                                </div>
                            </div>
                            <div class="space-y-4 text-sm text-white/85">
                                <div class="rounded-lg bg-heqamis-blue/60 p-4 border-l-4 border-heqamis-green">
                                    <p class="font-semibold text-white">1. Set up institution data</p>
                                    <p class="mt-1">Profile, governance, staff, students, and programmes.</p>
                                </div>
                                <div class="rounded-lg bg-heqamis-blue/60 p-4 border-l-4 border-heqamis-green">
                                    <p class="font-semibold text-white">2. Complete assessments</p>
                                    <p class="mt-1">Score criteria using NCHE rubrics and attach evidence.</p>
                                </div>
                                <div class="rounded-lg bg-heqamis-blue/60 p-4 border-l-4 border-heqamis-green">
                                    <p class="font-semibold text-white">3. Generate reports</p>
                                    <p class="mt-1">Export Self-Assessment Reports and annual compliance reports.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-heqamis-blue-light/60 bg-heqamis-blue-light/30">
            <div class="mx-auto max-w-6xl px-6 py-6 text-center text-sm text-white/70">
                Powered by <span class="font-semibold text-heqamis-green">EDUC Consultancy</span>
            </div>
        </footer>
    </body>
</html>
