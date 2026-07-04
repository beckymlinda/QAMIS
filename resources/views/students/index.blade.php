<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">Student Management</h2>
                <p class="mt-1 text-sm text-gray-600">All enrolled students at {{ $institution->name }}.</p>
            </div>
            @can('create', App\Models\Student::class)
                <a href="{{ route('students.create', request()->only('programme_id')) }}" class="inline-flex rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1a3a5c]">
                    Add student
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Name, student number, or email" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="sm:w-64">
                <label class="block text-xs font-medium text-gray-600 mb-1">Programme</label>
                <select name="programme_id" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">All programmes</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" @selected(request('programme_id') == $programme->id)>{{ $programme->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Student #</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Programme</th>
                        <th class="px-4 py-3 text-left">Phone</th>
                        <th class="px-4 py-3 text-left">Year</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-medium">{{ $student->student_number }}</td>
                            <td class="px-4 py-3">{{ $student->fullName() }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $student->programme?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $student->phone ?? '—' }}</td>
                            <td class="px-4 py-3">Year {{ $student->year_of_study }}</td>
                            <td class="px-4 py-3 capitalize">{{ $student->status ?? 'active' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $student) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">View</a>
                                @can('update', $student)
                                    <span class="text-gray-300 mx-1">|</span>
                                    <a href="{{ route('students.edit', $student) }}" class="text-[#0f2744] hover:text-[#8cc63f] font-medium">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No students found. Use <strong>Add student</strong> to register the first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $students->links() }}</div>
        </div>
    </div>
</x-app-layout>
