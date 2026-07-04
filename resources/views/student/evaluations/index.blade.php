<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Course & Lecturer Evaluations</h2>
            <a href="{{ route('student.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')

        @if(!$period)
            <div class="bg-white rounded-lg shadow p-6 text-gray-600">
                No evaluation period is currently open. Evaluations are available at the end of each semester as announced by your institution.
            </div>
        @else
            <div class="rounded-lg border border-[#8cc63f]/40 bg-[#8cc63f]/10 p-4 text-sm">
                <strong>{{ $period->title }}</strong> — open until {{ $period->closes_at->format('d M Y, H:i') }}.
                Your responses are anonymous and cannot be edited after submission.
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Course</th>
                            <th class="px-4 py-3 text-left">Lecturer</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium">{{ $item['offering']->course->code }} — {{ $item['offering']->course->title }}</td>
                                <td class="px-4 py-3">{{ $item['offering']->lecturer?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($item['status'] === 'submitted')
                                        <span class="text-green-600 font-medium">Submitted</span>
                                    @elseif($item['status'] === 'draft')
                                        <span class="text-amber-600">In progress</span>
                                    @else
                                        <span class="text-gray-500">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($item['status'] === 'submitted')
                                        <span class="text-gray-400">Done</span>
                                    @else
                                        <a href="{{ route('student.evaluations.show', $item['offering']) }}" class="font-medium text-[#0f2744] hover:text-[#8cc63f]">Evaluate</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No enrolled courses for this evaluation period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
