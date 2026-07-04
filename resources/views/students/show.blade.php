<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">{{ $student->fullName() }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $student->student_number }} · {{ $student->programme?->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('students.courses', $student) }}" class="inline-flex rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">View courses</a>
                @can('update', $student)
                    <a href="{{ route('students.edit', $student) }}" class="inline-flex rounded-lg border border-[#0f2744] px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-[#0f2744]/5">Edit</a>
                @endcan
                <a href="{{ route('students.index', ['programme_id' => $student->programme_id]) }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">← All students</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-sm font-semibold text-[#0f2744]">Student details</h3>
            </div>
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Full name</dt>
                    <dd class="sm:col-span-2 font-medium text-[#0f2744]">{{ $student->fullName() }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Student number</dt>
                    <dd class="sm:col-span-2">{{ $student->student_number }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="sm:col-span-2">{{ $student->phone ?? '—' }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Email (portal login)</dt>
                    <dd class="sm:col-span-2">{{ $student->email }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Programme</dt>
                    <dd class="sm:col-span-2">{{ $student->programme?->name ?? '—' }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Year of study</dt>
                    <dd class="sm:col-span-2">Year {{ $student->year_of_study }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="sm:col-span-2 capitalize">{{ $student->status ?? 'active' }}</dd>
                </div>
                <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-gray-500">Portal account</dt>
                    <dd class="sm:col-span-2">{{ $student->user ? 'Active' : 'No login linked' }}</dd>
                </div>
            </dl>
        </div>

        @can('delete', $student)
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Remove this student and their portal account?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Remove student</button>
                </form>
            </div>
        @endcan
    </div>
</x-app-layout>
