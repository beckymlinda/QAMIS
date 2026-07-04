<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">Evaluation Report</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $programme->name }} · {{ $period->title }}</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('programmes.academic.index', ['programme' => $programme, 'tab' => 'evaluations']) }}" class="text-[#0f2744] hover:text-[#8cc63f]">← Back to evaluations</a>
                <a href="{{ route('programmes.evaluation-periods.report', [$programme, $period]) }}" class="inline-flex items-center rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744] hover:bg-[#8cc63f]/90">Generate report (PDF)</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="rounded-lg border border-[#8cc63f]/40 bg-[#0f2744]/5 px-5 py-4 text-sm text-gray-700">
            <p class="font-semibold text-[#0f2744]">Students' Evaluation of Teaching Questionnaire</p>
            <p class="mt-1">{{ $period->academic_year }} · Semester {{ $period->semester }} · {{ $total_submissions }} anonymous submission{{ $total_submissions === 1 ? '' : 's' }}</p>
            <p class="mt-2 text-xs text-gray-500">Rating scale: 1 = Strongly Disagree, 2 = Disagree, 3 = Neutral, 4 = Agree, 5 = Strongly Agree</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Period status</p>
                <p class="mt-1 text-lg font-semibold text-[#0f2744]">{{ $period->isOpen() ? 'Open' : 'Closed' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total responses</p>
                <p class="mt-1 text-lg font-semibold text-[#0f2744]">{{ $total_submissions }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Courses evaluated</p>
                <p class="mt-1 text-lg font-semibold text-[#0f2744]">{{ $offerings->where('response_count', '>', 0)->count() }}</p>
            </div>
        </div>

        @if($total_submissions === 0)
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                No submitted evaluations yet for this programme during this period.
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">A. Course Evaluation — Programme Summary</h3>
                @include('programmes.academic.evaluations.partials.section-summary', ['groups' => $programme_sections['course']])
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">B. Lecturer Evaluation — Programme Summary</h3>
                @include('programmes.academic.evaluations.partials.section-summary', ['groups' => $programme_sections['lecturer']])
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">Open-Ended Responses</h3>
                @include('programmes.academic.evaluations.partials.open-summary', ['items' => $programme_sections['open']])
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-[#0f2744]">Results by course</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Course</th>
                                <th class="px-4 py-3 text-left">Lecturer</th>
                                <th class="px-4 py-3 text-left">Responses</th>
                                <th class="px-4 py-3 text-left">Course avg</th>
                                <th class="px-4 py-3 text-left">Lecturer avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offerings as $item)
                                @if($item['response_count'] > 0)
                                    <tr class="border-t">
                                        <td class="px-4 py-3 font-medium">{{ $item['course_code'] }}<br><span class="text-gray-500 font-normal">{{ $item['course_title'] }}</span></td>
                                        <td class="px-4 py-3">{{ $item['lecturer_name'] }}</td>
                                        <td class="px-4 py-3">{{ $item['response_count'] }}</td>
                                        <td class="px-4 py-3">{{ $item['course_average'] !== null ? number_format($item['course_average'], 2).'/5' : '—' }}</td>
                                        <td class="px-4 py-3">{{ $item['lecturer_average'] !== null ? number_format($item['lecturer_average'], 2).'/5' : '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
