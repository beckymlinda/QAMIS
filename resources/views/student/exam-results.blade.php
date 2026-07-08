<x-app-layout>
    <div class="min-h-full bg-gradient-to-b from-slate-50 via-gray-50 to-gray-100/80">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-[#0f2744] sm:text-2xl">Exam Results</h1>
                    <p class="text-xs text-gray-500">Published semester grades</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="text-sm font-semibold text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
            </div>

            @if($periods->isEmpty())
                <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-100">
                    <i class="bi bi-file-earmark-bar-graph text-4xl text-gray-300" aria-hidden="true"></i>
                    <p class="mt-4 text-base font-medium text-[#0f2744]">No published results yet</p>
                    <p class="mt-2 text-sm text-gray-500">Results appear here once your lecturer publishes grades for a semester.</p>
                </div>
            @else
                @php
                    $standing = \App\Support\GpaGrading::semesterStanding($semesterGpa);
                    $standingClasses = $standing ? \App\Support\GpaGrading::toneClasses($standing['tone']) : null;
                @endphp

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <form method="GET" class="flex flex-wrap items-end gap-3">
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-gray-500">Year</label>
                                <select name="academic_year" class="rounded-lg border-gray-300 py-1.5 text-sm shadow-sm" onchange="this.form.submit()">
                                    @foreach($periods->pluck('academic_year')->unique() as $year)
                                        <option value="{{ $year }}" @selected($academicYear === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-gray-500">Semester</label>
                                <select name="semester" class="rounded-lg border-gray-300 py-1.5 text-sm shadow-sm" onchange="this.form.submit()">
                                    @foreach($periods->where('academic_year', $academicYear)->pluck('semester')->unique() as $sem)
                                        <option value="{{ $sem }}" @selected((int) $semester === (int) $sem)>Semester {{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        @if($standing)
                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <span class="text-xs font-medium text-gray-500">Semester GPA</span>
                                <span class="text-lg font-bold leading-none {{ $standingClasses['text'] }}">{{ number_format($standing['gpa'], 2) }}</span>
                                <x-grade-tone-badge :tone="$standing['tone']" :label="$standing['label']" />
                            </div>
                        @endif
                    </div>

                    <div class="border-b border-gray-100 px-4 py-2.5 sm:px-5">
                        <h2 class="text-sm font-bold text-[#0f2744]">Course results</h2>
                        <p class="text-xs text-gray-500">{{ $academicYear }} · Semester {{ $semester }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50/80">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-5">Course</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">CH</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">Coursework</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">Exam</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">Final</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">Grade</th>
                                    <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">GP</th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-5">Decision</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($results as $enrolment)
                                    @php
                                        $r = $enrolment->result;
                                        $course = $enrolment->courseOffering->course;
                                        $tone = \App\Support\GpaGrading::toneForLetter($r->letter_grade);
                                        $toneClasses = \App\Support\GpaGrading::toneClasses($tone);
                                        $passed = \App\Support\GpaGrading::hasPassed($r->letter_grade);
                                    @endphp
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3 sm:px-5">
                                            <p class="font-semibold text-[#0f2744]">{{ $course->code }}</p>
                                            <p class="text-xs text-gray-500">{{ $course->title }}</p>
                                        </td>
                                        <td class="px-3 py-3 text-gray-600">{{ $course->credit_hours }}</td>
                                        <td class="px-3 py-3 text-gray-600">{{ $r->coursework_percentage !== null ? number_format($r->coursework_percentage, 1).'%' : '—' }}</td>
                                        <td class="px-3 py-3 text-gray-600">{{ $r->exam_percentage !== null ? number_format($r->exam_percentage, 1).'%' : '—' }}</td>
                                        <td class="px-3 py-3 font-medium text-[#0f2744]">{{ number_format($r->final_percentage, 1) }}%</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex rounded-lg px-2 py-0.5 text-xs font-bold ring-1 {{ $toneClasses['badge'] }}">
                                                {{ $r->letter_grade }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="font-semibold {{ $toneClasses['text'] }}">{{ number_format($r->grade_points, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 sm:px-5">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs text-gray-600">{{ $r->academic_decision }}</span>
                                                <x-grade-tone-badge :tone="$tone" :label="$passed ? 'Pass' : 'Fail'" class="w-fit text-[10px]" />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-10 text-center text-gray-500">No results for this semester.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
