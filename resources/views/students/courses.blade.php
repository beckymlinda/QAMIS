<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">Courses — {{ $student->fullName() }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $student->programme?->name }} · {{ $period['academic_year'] }} Semester {{ $period['semester'] }}</p>
            </div>
            <a href="{{ route('students.show', $student) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Student profile</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @if($periodOptions->count() > 1)
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Academic year</label>
                    <select name="academic_year" class="rounded-md border-gray-300 text-sm">
                        @foreach($periodOptions->pluck('academic_year')->unique() as $year)
                            <option value="{{ $year }}" @selected($period['academic_year'] === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Semester</label>
                    <select name="semester" class="rounded-md border-gray-300 text-sm">
                        @foreach([1, 2] as $sem)
                            <option value="{{ $sem }}" @selected($period['semester'] === $sem)>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white">View period</button>
            </form>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-[#0f2744]">Registered courses & performance</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Course</th>
                        <th class="px-4 py-3 text-left">Lecturer</th>
                        <th class="px-4 py-3 text-left">Coursework</th>
                        <th class="px-4 py-3 text-left">Exam</th>
                        <th class="px-4 py-3 text-left">Final</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrolments as $enrolment)
                        @php $result = $enrolment->result; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $enrolment->courseOffering?->course?->code }}</span>
                                <span class="text-gray-500"> — {{ $enrolment->courseOffering?->course?->title }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $enrolment->courseOffering?->lecturer?->name ?? 'TBA' }}</td>
                            <td class="px-4 py-3">{{ $result?->coursework_percentage ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $result?->exam_percentage ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $result?->final_percentage ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $result?->letter_grade ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $enrolment->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No course registrations for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
