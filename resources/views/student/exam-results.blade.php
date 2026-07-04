<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Exam Results</h2>
            <a href="{{ route('student.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if($periods->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">No published results yet. Results appear here once your lecturer publishes grades for a semester.</div>
        @else
            <form method="GET" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-4 items-end text-sm">
                <div>
                    <label class="block text-gray-600 mb-1">Academic year</label>
                    <select name="academic_year" class="rounded-md border-gray-300" onchange="this.form.submit()">
                        @foreach($periods->pluck('academic_year')->unique() as $year)
                            <option value="{{ $year }}" @selected($academicYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Semester</label>
                    <select name="semester" class="rounded-md border-gray-300" onchange="this.form.submit()">
                        @foreach($periods->where('academic_year', $academicYear)->pluck('semester')->unique() as $sem)
                            <option value="{{ $sem }}" @selected((int)$semester === (int)$sem)>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if($semesterGpa !== null)
                <div class="rounded-lg border border-[#8cc63f]/40 bg-[#8cc63f]/10 p-4 text-sm">
                    <strong>Semester GPA (sGPA):</strong> {{ number_format($semesterGpa, 2) }}
                    <span class="text-gray-600">— weighted by course credit hours per Assessment Rules §6.1</span>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-3 text-left">Course</th>
                        <th class="px-4 py-3 text-left">CH</th>
                        <th class="px-4 py-3 text-left">Coursework</th>
                        <th class="px-4 py-3 text-left">Exam</th>
                        <th class="px-4 py-3 text-left">Final</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-left">GP</th>
                        <th class="px-4 py-3 text-left">Decision</th>
                    </tr></thead>
                    <tbody>
                        @forelse($results as $enrolment)
                            @php $r = $enrolment->result; $course = $enrolment->courseOffering->course; @endphp
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium">{{ $course->code }}<br><span class="text-gray-500 font-normal">{{ $course->title }}</span></td>
                                <td class="px-4 py-3">{{ $course->credit_hours }}</td>
                                <td class="px-4 py-3">{{ $r->coursework_percentage !== null ? number_format($r->coursework_percentage, 1).'%' : '—' }}</td>
                                <td class="px-4 py-3">{{ $r->exam_percentage !== null ? number_format($r->exam_percentage, 1).'%' : '—' }}</td>
                                <td class="px-4 py-3">{{ number_format($r->final_percentage, 1) }}%</td>
                                <td class="px-4 py-3 font-semibold">{{ $r->letter_grade }}</td>
                                <td class="px-4 py-3">{{ number_format($r->grade_points, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $r->academic_decision }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No results for this semester.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
