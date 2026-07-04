<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">My Courses</h2>
            <a href="{{ route('student.dashboard') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Portal home</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="rounded-lg border border-[#0f2744]/10 bg-[#0f2744]/5 p-4 text-sm text-gray-700">
            <p class="font-medium text-[#0f2744]">{{ $period['academic_year'] }} · Semester {{ $period['semester'] }}</p>
            <p class="mt-1">Register only for courses prescribed for your programme and semester. Courses outside your approved curriculum are not available unless your institution grants an override.</p>
        </div>

        <section class="space-y-4">
            <h3 class="text-sm font-semibold text-[#0f2744] uppercase tracking-wide">Registered courses</h3>
            @forelse($enrolled as $offering)
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div>
                            <h4 class="font-semibold text-[#0f2744]">{{ $offering->course->code }} — {{ $offering->course->title }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $offering->course->credit_hours }} credit hours · {{ str_replace('_', ' ', $offering->delivery_mode) }}</p>
                        </div>
                        <div class="text-sm text-right">
                            <p><span class="text-gray-500">Lecturer:</span> {{ $offering->lecturer?->name ?? 'TBA' }}</p>
                            <a href="{{ route('student.lms.show', $offering) }}" class="inline-block mt-2 rounded-lg bg-[#0f2744] px-4 py-2 text-sm font-medium text-white">Open LMS</a>
                            @if(!$lockedOfferingIds->contains($offering->id))
                                <form method="POST" action="{{ route('student.courses.drop', $offering) }}" class="mt-2" onsubmit="return confirm('Drop this course registration?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="academic_year" value="{{ $period['academic_year'] }}">
                                    <input type="hidden" name="semester" value="{{ $period['semester'] }}">
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Drop course</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">You have not registered for any courses this semester.</div>
            @endforelse
        </section>

        @if($available->isNotEmpty())
            <section class="space-y-4">
                <h3 class="text-sm font-semibold text-[#0f2744] uppercase tracking-wide">Available for registration</h3>
                @foreach($available as $offering)
                    <div class="bg-white rounded-lg shadow p-5 border border-dashed border-[#8cc63f]/40">
                        <div class="flex flex-wrap justify-between gap-3 items-center">
                            <div>
                                <h4 class="font-semibold text-[#0f2744]">{{ $offering->course->code }} — {{ $offering->course->title }}</h4>
                                <p class="text-sm text-gray-500 mt-1">{{ $offering->course->credit_hours }} credit hours · Lecturer: {{ $offering->lecturer?->name ?? 'TBA' }}</p>
                            </div>
                            <form method="POST" action="{{ route('student.courses.register') }}">
                                @csrf
                                <input type="hidden" name="course_offering_id" value="{{ $offering->id }}">
                                <input type="hidden" name="academic_year" value="{{ $period['academic_year'] }}">
                                <input type="hidden" name="semester" value="{{ $period['semester'] }}">
                                <button type="submit" class="rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">Register</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif
    </div>
</x-app-layout>
