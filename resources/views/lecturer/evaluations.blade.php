<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Teaching Evaluations</h2>
            <a href="{{ route('lecturer.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <p class="text-sm text-gray-600 mb-4">Anonymous student evaluation submissions per course (SRS: lecturers access own evaluation reports).</p>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Course</th><th class="px-4 py-3 text-left">Semester</th><th class="px-4 py-3 text-left">Submitted evaluations</th></tr></thead>
                <tbody>
                    @forelse($summaries as $item)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-medium">{{ $item['offering']->course->code }} — {{ $item['offering']->course->title }}</td>
                            <td class="px-4 py-3">{{ $item['offering']->academic_year }} Sem {{ $item['offering']->semester }}</td>
                            <td class="px-4 py-3">{{ $item['response_count'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No evaluation data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
