<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Grades</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $enrolment->student->fullName() }}</h2>
            </div>
            <a href="{{ route('lecturer.lms.grades', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to grades</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'grades', 'role' => 'lecturer'])

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Weighted coursework</p>
                <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ number_format($summary['weighted_coursework_contribution'], 1) }}<span class="text-base font-medium text-gray-500"> / {{ \App\Services\CourseGradeCalculator::COURSEWORK_PORTION_PERCENT }}%</span></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Exam contribution</p>
                <p class="mt-2 text-2xl font-bold text-[#0f2744]">
                    @if($summary['exam_contribution'] !== null)
                        {{ number_format($summary['exam_contribution'], 1) }}<span class="text-base font-medium text-gray-500"> / {{ \App\Support\GpaGrading::examPortionPercent() }}%</span>
                    @else
                        —
                    @endif
                </p>
                @if($summary['exam_percentage'] !== null)
                    <p class="mt-1 text-xs text-gray-500">Exam score {{ number_format($summary['exam_percentage'], 1) }}%</p>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Combined total</p>
                <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ $summary['combined_total_percentage'] !== null ? number_format($summary['combined_total_percentage'], 1).'%' : '—' }}</p>
                <p class="mt-1 text-xs text-gray-500">Coursework + exam contributions</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Course GPA</p>
                @if($summary['course_gpa'])
                    <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ $summary['course_gpa']['letter'] }} <span class="text-base font-medium text-gray-500">({{ number_format($summary['course_gpa']['points'], 2) }} GP)</span></p>
                    <p class="mt-1 text-xs text-gray-500">{{ $summary['course_gpa']['quality'] }} · {{ $summary['course_gpa']['decision'] }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Enter exam mark to calculate</p>
                @endif
            </div>
        </div>

        <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-100">
            <strong>Semester GPA:</strong> {{ $semesterGpa !== null ? number_format($semesterGpa, 2) : '—' }}
            <span class="text-gray-500">· Weighted average of grade points × credit hours (Table 1 scale)</span>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="font-semibold text-[#0f2744]">Assignment breakdown</h3>
                <p class="mt-1 text-xs text-gray-500">Each row shows how much the assignment adds toward the {{ \App\Support\GpaGrading::courseworkPortionPercent() }}% coursework portion.</p>
            </div>
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Weight</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Contribution</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Performance band</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($breakdown['lines'] as $line)
                        <tr>
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $line['assignment']->title }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                @if($line['score'] !== null)
                                    {{ $line['score'] }}/{{ $line['assignment']->max_score }}
                                    <span class="text-xs text-gray-500">({{ number_format($line['assignment_percentage'], 1) }}%)</span>
                                @else
                                    <span class="text-gray-400">Not graded</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ number_format($line['weight_percent'], 1) }}%</td>
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ number_format($line['contribution_to_course'], 2) }}%</td>
                            <td class="px-6 py-4">
                                @if($line['contribution_to_gpa'])
                                    {{ $line['contribution_to_gpa']['letter'] }} ({{ number_format($line['contribution_to_gpa']['points'], 2) }})
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No published assignments yet.</td></tr>
                    @endforelse
                </tbody>
                @if($breakdown['lines'])
                    <tfoot class="border-t border-gray-200 bg-gray-50/80">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Total coursework contribution</td>
                            <td class="px-6 py-3 font-bold text-[#0f2744]">{{ number_format($summary['weighted_coursework_contribution'], 2) }}%</td>
                            <td class="px-6 py-3 text-xs text-gray-500">
                                @if($summary['coursework_performance_average'] !== null)
                                    Avg performance {{ number_format($summary['coursework_performance_average'], 1) }}%
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div id="edit-grade" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="font-semibold text-[#0f2744]">Final grade &amp; exam</h3>
                <p class="mt-1 text-xs text-gray-500">Coursework contribution is calculated from assignments. Enter the exam score to complete the combined total and course GPA.</p>
            </div>
            <form method="POST" action="{{ route('lecturer.lms.grades.update', [$offering, $enrolment]) }}" class="space-y-4 px-6 py-5">
                @csrf @method('PUT')
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Weighted coursework contribution</label>
                        <input type="text" readonly value="{{ number_format($summary['weighted_coursework_contribution'], 1) }}% of {{ \App\Services\CourseGradeCalculator::COURSEWORK_PORTION_PERCENT }}%" class="mt-1 w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Final exam score (0–100)</label>
                        <input type="number" step="0.01" min="0" max="100" name="exam_percentage" value="{{ old('exam_percentage', $result?->exam_percentage) }}" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                        <p class="mt-1 text-xs text-gray-500">Counts for {{ \App\Support\GpaGrading::examPortionPercent() }}% of the course</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Combined total (auto)</label>
                        <input type="text" readonly value="{{ $summary['combined_total_percentage'] !== null ? number_format($summary['combined_total_percentage'], 1).'%' : 'Enter exam to calculate' }}" class="mt-1 w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700 shadow-sm">
                        @if($summary['course_gpa'])
                            <p class="mt-1 text-xs text-gray-500">Course GPA: {{ $summary['course_gpa']['letter'] }} ({{ number_format($summary['course_gpa']['points'], 2) }} GP)</p>
                        @endif
                    </div>
                </div>
                <div class="rounded-xl border border-dashed border-gray-200 p-4 space-y-3">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="use_final_override" value="1" @checked(old('use_final_override', $result?->use_final_override))>
                        Override combined final percentage manually
                    </label>
                    <input type="number" step="0.01" min="0" max="100" name="final_percentage_override" value="{{ old('final_percentage_override', $result?->final_percentage_override) }}" placeholder="Final % override" class="w-full max-w-xs rounded-xl border-gray-300 shadow-sm">
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-xl bg-[#0f2744] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1a3a5c]">Save grade</button>
                    <button type="submit" name="publish" value="1" class="rounded-xl bg-[#8cc63f] px-5 py-2.5 text-sm font-semibold text-[#0f2744] hover:bg-[#7ab535]">Save &amp; publish to student</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
