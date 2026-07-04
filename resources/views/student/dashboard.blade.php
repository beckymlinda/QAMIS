<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-[#0f2744]">Student Portal</h2>
            <p class="text-sm text-gray-500 mt-1">Welcome, {{ $student->fullName() }}</p>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Programme</p>
                <p class="text-lg font-semibold text-[#0f2744]">{{ $student->programme->name }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Student number</p>
                <p class="text-lg font-semibold text-[#0f2744]">{{ $student->student_number }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Year of study</p>
                <p class="text-lg font-semibold text-[#0f2744]">Year {{ $student->year_of_study }}</p>
            </div>
        </div>

        @if($period)
            <div class="rounded-lg border border-[#8cc63f]/50 bg-[#8cc63f]/10 p-5">
                <h3 class="font-semibold text-[#0f2744]">Teaching evaluation period open</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $period->title }} — closes {{ $period->closes_at->format('d M Y, H:i') }}</p>
                @if($pendingCount > 0)
                    <p class="text-sm mt-2 text-[#0f2744]"><strong>{{ $pendingCount }}</strong> course evaluation(s) pending.</p>
                    <a href="{{ route('student.evaluations') }}" class="inline-block mt-3 rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white">Complete evaluations</a>
                @else
                    <p class="text-sm mt-2 text-green-700">All evaluations submitted for this period. Thank you.</p>
                @endif
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-[#0f2744] mb-3">Quick links</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('student.timetable') }}" class="rounded-lg border border-[#0f2744]/20 px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-gray-50">My timetable</a>
                    <a href="{{ route('student.courses') }}" class="rounded-lg border border-[#0f2744]/20 px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-gray-50">My courses</a>
                    <a href="{{ route('student.notifications') }}" class="rounded-lg border border-[#0f2744]/20 px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-gray-50">Notifications</a>
            <a href="{{ route('student.exam-results') }}" class="rounded-lg border border-[#0f2744]/20 px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-gray-50">Exam results</a>
                    <a href="{{ route('student.profile') }}" class="rounded-lg border border-[#0f2744]/20 px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-gray-50">My profile</a>
                    <a href="{{ route('student.evaluations') }}" class="rounded-lg border border-[#8cc63f] bg-[#8cc63f]/20 px-4 py-2 text-sm font-medium text-[#0f2744]">Evaluate lecturers</a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-[#0f2744] mb-3">Upcoming classes</h3>
                <ul class="text-sm space-y-2">
                    @forelse($upcomingSlots as $slot)
                        @php $offering = $slot->courseOffering; @endphp
                        <li class="border-b border-gray-100 pb-2">
                            <strong>{{ $offering->course->code }}</strong> — {{ $slot->dayName() }} {{ substr($slot->start_time, 0, 5) }}
                            <span class="text-gray-500">@ {{ $slot->venueLabel() }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">No timetable slots scheduled yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
