<x-guest-layout>
    <x-slot name="title">Register</x-slot>
    <x-slot name="heading">Register your institution</x-slot>
    <x-slot name="subheading">Create an account for your institution to manage report data, assessments, and reports.</x-slot>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="institution_name" :value="__('Institution name')" class="text-heqamis-blue" />
            <x-text-input id="institution_name" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green" type="text" name="institution_name" :value="old('institution_name')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('institution_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="institution_acronym" :value="__('Acronym (optional)')" class="text-heqamis-blue" />
            <x-text-input id="institution_acronym" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green" type="text" name="institution_acronym" :value="old('institution_acronym')" maxlength="50" />
            <x-input-error :messages="$errors->get('institution_acronym')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="name" :value="__('Your name')" class="text-heqamis-blue" />
            <x-text-input id="name" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-heqamis-blue" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-heqamis-blue" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-heqamis-blue" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-heqamis-green focus:ring-heqamis-green"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm text-heqamis-blue hover:text-heqamis-green underline" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-heqamis-green px-6 py-2.5 text-sm font-semibold text-heqamis-blue shadow-sm hover:bg-heqamis-green-dark focus:outline-none focus:ring-2 focus:ring-heqamis-green focus:ring-offset-2 transition">
                {{ __('Create institution account') }}
            </button>
        </div>
    </form>
</x-guest-layout>
