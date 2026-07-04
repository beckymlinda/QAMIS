<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Add student</h2>
            <a href="{{ route('students.index', request()->only('programme_id')) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Back to students</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        <div class="bg-white shadow rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-4">
                Add a new student and give them portal access. If you leave student number, email, or password blank, the system will fill them in automatically
                (for example, email like <strong>{{ $defaultEmailExample }}</strong> and password <strong>password</strong>).
            </p>

            <form method="POST" action="{{ route('students.store') }}">
                @csrf
                @include('students.partials.form', [
                    'institution' => $institution,
                    'programmes' => $programmes,
                    'selectedProgrammeId' => $selectedProgrammeId,
                    'defaultEmailExample' => $defaultEmailExample,
                ])
                <div class="mt-4 flex gap-3 border-t border-gray-100 pt-4">
                    <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Register student</button>
                    <a href="{{ route('students.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
