<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <a href="{{ route('lecturer.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
            <h2 class="font-semibold text-xl text-[#0f2744]">Students — {{ $offering->course->code }}</h2>
            <p class="text-sm text-gray-500">{{ $offering->course->title }} · {{ $offering->academic_year }} Sem {{ $offering->semester }}</p>
        </div>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')
        <div class="flex justify-end">
            <a href="{{ route('lecturer.lms.grades', $offering) }}" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Enter / publish grades</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">Student #</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Year</th><th class="px-4 py-3 text-left">Grade</th><th class="px-4 py-3 text-left">Status</th>
                </tr></thead>
                <tbody>
                    @forelse($offering->studentEnrolments as $enrolment)
                        @php $result = $enrolment->result; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $enrolment->student->student_number }}</td>
                            <td class="px-4 py-3">{{ $enrolment->student->fullName() }}</td>
                            <td class="px-4 py-3">Year {{ $enrolment->student->year_of_study }}</td>
                            <td class="px-4 py-3">{{ $result?->letter_grade ?? '—' }} @if($result)<span class="text-gray-500">({{ number_format($result->final_percentage, 1) }}%)</span>@endif</td>
                            <td class="px-4 py-3">@if($result?->is_published)<span class="text-green-600">Published</span>@elseif($result)<span class="text-amber-600">Draft</span>@else<span class="text-gray-400">Not graded</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No students enrolled.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
