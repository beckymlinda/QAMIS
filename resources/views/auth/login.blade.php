<x-guest-layout>
    <x-slot name="title">Log in</x-slot>
    <x-slot name="heading">Welcome back</x-slot>
    <x-slot name="subheading">Sign in to {{ config('app.short_name') }}.</x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-heqamis-blue" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-heqamis-blue" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-heqamis-green shadow-sm focus:ring-heqamis-green" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-heqamis-blue hover:text-heqamis-green underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-heqamis-green px-6 py-2.5 text-sm font-semibold text-heqamis-blue shadow-sm hover:bg-heqamis-green-dark focus:outline-none focus:ring-2 focus:ring-heqamis-green focus:ring-offset-2 transition">
                {{ __('Log in') }}
            </button>
        </div>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-gray-600">
            Need an institution account?
            <a href="{{ route('register') }}" class="font-medium text-heqamis-blue hover:text-heqamis-green underline">Register</a>
        </p>
    @endif
</x-guest-layout>
