@php
    $tabs = [
        'courses' => ['label' => 'Courses', 'count' => $programme->courses->count()],
        'lecturers' => ['label' => 'Lecturers', 'count' => $lecturers->count()],
        'offerings' => ['label' => 'Offerings', 'count' => $programme->courses->sum(fn ($c) => $c->offerings->count())],
        'students' => ['label' => 'Students', 'count' => $programme->students->count()],
        'venues' => ['label' => 'Venues', 'count' => $classrooms->count()],
        'timetable' => ['label' => 'Timetable', 'count' => $programme->courses->sum(fn ($c) => $c->offerings->sum(fn ($o) => $o->timetableSlots->count()))],
        'evaluations' => ['label' => 'Evaluations', 'count' => $evaluationPeriods->count()],
    ];
    $activeTab = request('tab', 'courses');
    if (! array_key_exists($activeTab, $tabs)) {
        $activeTab = 'courses';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">Course & Student Management</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $programme->name }}</p>
            </div>
            <a href="{{ route('programmes.show', $programme) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to programme</a>
        </div>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4" x-data="{ tab: '{{ $activeTab }}' }">
        @include('partials.alerts')

        <div class="rounded-lg border border-[#8cc63f]/40 bg-[#0f2744]/5 px-5 py-4 text-sm text-gray-700">
            Set up courses, staff, students, timetables, and evaluations for this programme. Published data appears in the student and lecturer portals.
        </div>

        {{-- Sticky tab navigation --}}
        <div class="sticky top-16 z-20 -mx-4 sm:mx-0 bg-gray-100/95 backdrop-blur-sm sm:bg-transparent sm:backdrop-blur-none px-4 sm:px-0 py-2 sm:py-0">
            <nav class="flex gap-1 overflow-x-auto pb-1 sm:flex-wrap sm:gap-2" aria-label="Programme management sections">
                @foreach($tabs as $key => $meta)
                    <button
                        type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-[#0f2744] text-white shadow-sm'
                            : 'bg-white text-[#0f2744] border border-gray-200 hover:border-[#8cc63f]/60'"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                    >
                        {{ $meta['label'] }}
                        @if($meta['count'] > 0)
                            <span
                                class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-semibold"
                                :class="tab === '{{ $key }}' ? 'bg-[#8cc63f] text-[#0f2744]' : 'bg-gray-100 text-gray-600'"
                            >{{ $meta['count'] }}</span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        <div x-show="tab === 'courses'" x-cloak>
            @include('programmes.academic.partials.tab-courses')
        </div>
        <div x-show="tab === 'lecturers'" x-cloak>
            @include('programmes.academic.partials.tab-lecturers')
        </div>
        <div x-show="tab === 'offerings'" x-cloak>
            @include('programmes.academic.partials.tab-offerings')
        </div>
        <div x-show="tab === 'students'" x-cloak>
            @include('programmes.academic.partials.tab-students')
        </div>
        <div x-show="tab === 'venues'" x-cloak>
            @include('programmes.academic.partials.tab-venues')
        </div>
        <div x-show="tab === 'timetable'" x-cloak>
            @include('programmes.academic.partials.tab-timetable')
        </div>
        <div x-show="tab === 'evaluations'" x-cloak>
            @include('programmes.academic.partials.tab-evaluations')
        </div>
    </div>
</x-app-layout>
