<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#0f2744]">Students</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $programme->students->count() }} student{{ $programme->students->count() === 1 ? '' : 's' }} enrolled on this programme.</p>
            <p class="text-xs text-gray-500 mt-2">Students are registered centrally in Student Management and allocated to a programme there.</p>
        </div>
        @can('create', App\Models\Student::class)
            <a href="{{ route('students.create', ['programme_id' => $programme->id]) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f2744]/90">
                Add student
            </a>
        @endcan
    </div>

    <div class="mb-4">
        <a href="{{ route('students.index', ['programme_id' => $programme->id]) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">Open Student Management →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-3 py-2 text-left">Student #</th>
                <th class="px-3 py-2 text-left">Name</th>
                <th class="px-3 py-2 text-left">Email</th>
                <th class="px-3 py-2 text-left">Year</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($programme->students as $student)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-medium">{{ $student->student_number }}</td>
                        <td class="px-3 py-2">{{ $student->fullName() }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $student->email }}</td>
                        <td class="px-3 py-2">Year {{ $student->year_of_study }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('students.show', $student) }}" class="text-[#0f2744] text-xs font-medium hover:text-[#8cc63f]">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">No students on this programme yet. Use <strong>Student Management</strong> to register students.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
