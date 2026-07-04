<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HEQAMIS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 h-screen overflow-hidden">
        <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="h-screen">
            @include('layouts.sidebar')

            <div
                class="app-content-area flex h-screen w-full flex-col overflow-y-auto transition-all duration-300"
                :class="sidebarCollapsed ? 'sidebar-collapsed' : ''"
            >
                @include('layouts.topbar')

                @isset($header)
                    <div class="shrink-0 border-b border-gray-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @isset($scripts)
            {{ $scripts }}
        @endisset
        @stack('scripts')
    </body>
</html>
