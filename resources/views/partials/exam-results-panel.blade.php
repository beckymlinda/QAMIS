@props(['results', 'periods', 'academicYear', 'semester', 'semesterGpa', 'cumulativeGpa' => null, 'formAction' => null])

@if($periods->isEmpty())
    <div class="rounded-xl bg-gray-50 p-8 text-center text-sm text-gray-500">No published results yet.</div>
@else
    @php
        $standing = \App\Support\GpaGrading::semesterStanding($semesterGpa);
        $standingClasses = $standing ? \App\Support\GpaGrading::toneClasses($standing['tone']) : null;
    @endphp

    <div class="overflow-hidden rounded-xl border border-gray-200">
        <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ $formAction }}" class="flex flex-wrap items-end gap-3">
                @if($formAction)
                    <input type="hidden" name="tab" value="grades">
                @endif
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-gray-500">Year</label>
                    <select name="academic_year" class="rounded-lg border-gray-300 py-1.5 text-sm" onchange="this.form.submit()">
                        @foreach($periods->pluck('academic_year')->unique() as $year)
                            <option value="{{ $year }}" @selected($academicYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-gray-500">Semester</label>
                    <select name="semester" class="rounded-lg border-gray-300 py-1.5 text-sm" onchange="this.form.submit()">
                        @foreach($periods->where('academic_year', $academicYear)->pluck('semester')->unique() as $sem)
                            <option value="{{ $sem }}" @selected((int) $semester === (int) $sem)>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                @if($cumulativeGpa !== null)
                    <div class="text-right">
                        <p class="text-[10px] font-semibold uppercase text-gray-500">Overall GPA</p>
                        <p class="text-lg font-bold text-[#0f2744]">{{ number_format($cumulativeGpa, 2) }}</p>
                    </div>
                @endif
                @if($standing)
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-gray-500">Semester GPA</span>
                        <span class="text-lg font-bold {{ $standingClasses['text'] }}">{{ number_format($standing['gpa'], 2) }}</span>
                        <x-grade-tone-badge :tone="$standing['tone']" :label="$standing['label']" />
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Course</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">CH</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Coursework</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Exam</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Final</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Grade</th>
                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">GP</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold uppercase text-gray-500">Decision</th>
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
                            <td class="px-4 py-3"><p class="font-semibold text-[#0f2744]">{{ $course->code }}</p><p class="text-xs text-gray-500">{{ $course->title }}</p></td>
                            <td class="px-3 py-3 text-gray-600">{{ $course->credit_hours }}</td>
                            <td class="px-3 py-3 text-gray-600">{{ $r->coursework_percentage !== null ? number_format($r->coursework_percentage, 1).'%' : '—' }}</td>
                            <td class="px-3 py-3 text-gray-600">{{ $r->exam_percentage !== null ? number_format($r->exam_percentage, 1).'%' : '—' }}</td>
                            <td class="px-3 py-3 font-medium">{{ number_format($r->final_percentage, 1) }}%</td>
                            <td class="px-3 py-3"><span class="inline-flex rounded-lg px-2 py-0.5 text-xs font-bold ring-1 {{ $toneClasses['badge'] }}">{{ $r->letter_grade }}</span></td>
                            <td class="px-3 py-3 font-semibold {{ $toneClasses['text'] }}">{{ number_format($r->grade_points, 2) }}</td>
                            <td class="px-4 py-3"><span class="text-xs text-gray-600">{{ $r->academic_decision }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500">No results for this semester.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
