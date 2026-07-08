<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.short_name') }} — {{ $title ?? 'Account' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <div class="hidden lg:flex lg:w-1/2 bg-heqamis-blue text-white flex-col justify-between p-12">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    @if (file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.short_name') }}" class="h-14 w-auto object-contain">
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-heqamis-green text-lg font-bold text-heqamis-blue">E</span>
                    @endif
                    <span class="text-2xl font-bold tracking-wide">{{ config('app.short_name') }}</span>
                </a>

                <div>
                    <h1 class="text-3xl font-bold leading-tight">{{ config('app.full_name') }}</h1>
                    <p class="mt-4 text-white/80 text-lg max-w-md">
                        Self-assessment, compliance tracking, and reporting for institutional and programme accreditation.
                    </p>
                </div>

                <p class="text-sm text-white/60">Powered by EDUC Consultancy</p>
            </div>

            <div class="flex flex-1 flex-col justify-center bg-gray-50 px-6 py-12 sm:px-12 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 lg:hidden text-center">
                        <a href="{{ route('welcome') }}" class="inline-flex flex-col items-center gap-2">
                            @if (file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.short_name') }}" class="h-16 w-auto object-contain">
                            @else
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-heqamis-green text-lg font-bold text-heqamis-blue">E</span>
                            @endif
                            <span class="text-xl font-bold text-heqamis-blue">{{ config('app.short_name') }}</span>
                        </a>
                    </div>

                    @isset($heading)
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-heqamis-blue">{{ $heading }}</h2>
                            @isset($subheading)
                                <p class="mt-1 text-sm text-gray-600">{{ $subheading }}</p>
                            @endisset
                        </div>
                    @endisset

                    <div class="rounded-xl bg-white p-8 shadow-lg ring-1 ring-gray-200">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-gray-500 lg:hidden">Powered by EDUC Consultancy</p>
                </div>
            </div>
        </div>
    </body>
</html>
