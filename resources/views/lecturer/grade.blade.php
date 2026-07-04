<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <a href="{{ route('lecturer.offerings.students', $offering) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Students list</a>
            <h2 class="font-semibold text-xl text-[#0f2744]">Grade students — {{ $offering->course->code }}</h2>
            <p class="text-sm text-gray-500">Final mark = 40% coursework + 60% exam (GPA scale from Assessment Rules Table 1)</p>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('lecturer.offerings.grade.store', $offering) }}" class="bg-white rounded-lg shadow overflow-hidden">
            @csrf
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-left">Coursework %</th>
                    <th class="px-4 py-3 text-left">Exam %</th>
                    <th class="px-4 py-3 text-left">Current grade</th>
                </tr></thead>
                <tbody>
                    @foreach($offering->studentEnrolments as $index => $enrolment)
                        @php $result = $enrolment->result; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                {{ $enrolment->student->fullName() }}
                                <input type="hidden" name="grades[{{ $index }}][enrolment_id]" value="{{ $enrolment->id }}">
                            </td>
                            <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][coursework_percentage]" value="{{ old("grades.{$index}.coursework_percentage", $result?->coursework_percentage) }}" class="w-24 rounded-md border-gray-300"></td>
                            <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][exam_percentage]" value="{{ old("grades.{$index}.exam_percentage", $result?->exam_percentage) }}" class="w-24 rounded-md border-gray-300"></td>
                            <td class="px-4 py-3 text-gray-600">{{ $result?->letter_grade ?? '—' }} @if($result?->is_published)<span class="text-green-600 text-xs">(published)</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t flex flex-wrap items-center gap-4">
                <button type="submit" class="rounded-lg bg-[#0f2744] px-5 py-2 text-sm font-medium text-white">Save draft</button>
                <button type="submit" name="publish" value="1" class="rounded-lg bg-[#8cc63f] px-5 py-2 text-sm font-semibold text-[#0f2744]">Save & publish to students</button>
            </div>
        </form>
    </div>
</x-app-layout>
