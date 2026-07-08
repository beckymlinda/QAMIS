<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Grades</h2>
            </div>
            <a href="{{ route('lecturer.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'grades', 'role' => 'lecturer'])

        <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-900 ring-1 ring-blue-100">
            <strong>Assessment rules:</strong> Coursework = {{ \App\Support\GpaGrading::courseworkPortionPercent() }}% (from assignments) · Final exam = {{ \App\Support\GpaGrading::examPortionPercent() }}%.
            Assignments use <strong>{{ number_format($courseworkUsed, 1) }}%</strong> of the {{ \App\Support\GpaGrading::courseworkPortionPercent() }}% coursework allocation
            (<strong>{{ number_format($courseworkRemaining, 1) }}%</strong> remaining).
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Coursework (of 40%)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Course GPA</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Semester GPA</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($gradebook as $row)
                        @php
                            $enrolment = $row['enrolment'];
                            $breakdown = $row['breakdown'];
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $enrolment->student->fullName() }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ number_format($breakdown['earned_coursework_points'], 1) }} / {{ number_format(\App\Services\CourseGradeCalculator::COURSEWORK_PORTION_PERCENT, 0) }}%
                            </td>
                            <td class="px-6 py-4">
                                @if($row['letter_grade'])
                                    <span class="font-semibold text-[#0f2744]">{{ $row['letter_grade'] }}</span>
                                    <span class="text-xs text-gray-500">({{ number_format($row['grade_points'], 2) }} GP)</span>
                                    @if($row['final_percentage'] !== null)
                                        <span class="block text-xs text-gray-500">{{ number_format($row['final_percentage'], 1) }}% final</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $row['semester_gpa'] !== null ? number_format($row['semester_gpa'], 2) : '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('lecturer.lms.grades.show', [$offering, $enrolment]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</a>
                                    <a href="{{ route('lecturer.lms.grades.show', [$offering, $enrolment]) }}#edit-grade" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</a>
                                    @if($row['result'])
                                        <form method="POST" action="{{ route('lecturer.lms.grades.destroy', [$offering, $enrolment]) }}" onsubmit="return confirm('Remove recorded grade for this student?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
