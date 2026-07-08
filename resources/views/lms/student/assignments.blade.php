<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Assignments</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'assignments', 'role' => 'student'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Due date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($progress['assignments'] as $assignment)
                        @php $submission = $progress['submissions']->get($assignment->id); @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-[#0f2744]">{{ $assignment->title }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $assignment->due_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($submission?->submitted_at)
                                    Submitted {{ $submission->submitted_at->format('d M Y') }}
                                    @if($submission->isGraded())
                                        · Grade {{ $submission->score }}/{{ $assignment->max_score }}
                                    @endif
                                @else
                                    Not submitted
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <a href="{{ route('student.lms.assignments.show', [$offering, $assignment]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">No assignments published yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
