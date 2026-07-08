<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">My grades</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'grades', 'role' => 'student'])

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Weighted coursework</p>
                <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ number_format($summary['weighted_coursework_contribution'], 1) }}<span class="text-base font-medium text-gray-500"> / {{ \App\Services\CourseGradeCalculator::COURSEWORK_PORTION_PERCENT }}%</span></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Exam contribution</p>
                @if($showFinal && $summary['exam_contribution'] !== null)
                    <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ number_format($summary['exam_contribution'], 1) }}<span class="text-base font-medium text-gray-500"> / {{ \App\Support\GpaGrading::examPortionPercent() }}%</span></p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Not published yet</p>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Combined total</p>
                @if($showFinal && $summary['combined_total_percentage'] !== null)
                    <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ number_format($summary['combined_total_percentage'], 1) }}%</p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Awaiting final grade</p>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500">Course GPA</p>
                @if($showFinal && $summary['course_gpa'])
                    <p class="mt-2 text-2xl font-bold text-[#0f2744]">{{ $summary['course_gpa']['letter'] }} <span class="text-base font-medium text-gray-500">({{ number_format($summary['course_gpa']['points'], 2) }} GP)</span></p>
                    <p class="mt-1 text-xs text-gray-500">{{ $summary['course_gpa']['decision'] }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Not published yet</p>
                @endif
            </div>
        </div>

        @if($showFinal)
            <p class="text-sm text-gray-600">Semester GPA: <strong>{{ $semesterGpa !== null ? number_format($semesterGpa, 2) : '—' }}</strong></p>
        @endif

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="font-semibold text-[#0f2744]">Assignment performance</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Your score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Weight</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Contribution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($breakdown['lines'] as $line)
                        <tr>
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $line['assignment']->title }}</td>
                            <td class="px-6 py-4">
                                @if($line['score'] !== null)
                                    {{ $line['score'] }}/{{ $line['assignment']->max_score }}
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ number_format($line['weight_percent'], 1) }}%</td>
                            <td class="px-6 py-4">{{ number_format($line['contribution_to_course'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No assignments yet.</td></tr>
                    @endforelse
                </tbody>
                @if($breakdown['lines'])
                    <tfoot class="border-t border-gray-200 bg-gray-50/80">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Total coursework contribution</td>
                            <td class="px-6 py-3 font-bold text-[#0f2744]">{{ number_format($summary['weighted_coursework_contribution'], 2) }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
