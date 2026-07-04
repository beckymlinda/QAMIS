<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Edit student</h2>
            <a href="{{ route('students.show', $student) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Back to profile</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('students.update', $student) }}">
                @csrf
                @method('PUT')
                @include('students.partials.form', [
                    'student' => $student,
                    'institution' => $institution,
                    'programmes' => $programmes,
                    'defaultEmailExample' => $defaultEmailExample,
                ])
                <div class="mt-4 flex gap-3 border-t border-gray-100 pt-4">
                    <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                    <a href="{{ route('students.show', $student) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
