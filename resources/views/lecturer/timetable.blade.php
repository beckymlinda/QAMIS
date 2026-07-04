<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">My Timetable</h2>
            <a href="{{ route('lecturer.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">Day</th><th class="px-4 py-3 text-left">Time</th><th class="px-4 py-3 text-left">Course</th><th class="px-4 py-3 text-left">Venue</th><th class="px-4 py-3 text-left">Type</th>
                </tr></thead>
                <tbody>
                    @forelse($slots as $slot)
                        @php $offering = $slot->courseOffering; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $slot->dayName() }}</td>
                            <td class="px-4 py-3">{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</td>
                            <td class="px-4 py-3 font-medium">{{ $offering->course->code }}<br><span class="text-gray-500 font-normal">{{ $offering->course->title }}</span></td>
                            <td class="px-4 py-3">{{ $slot->venueLabel() }}</td>
                            <td class="px-4 py-3 capitalize">{{ $slot->session_type }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No timetable published.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
