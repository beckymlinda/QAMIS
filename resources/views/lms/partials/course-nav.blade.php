@php
    $active = $active ?? 'overview';
    $role = $role ?? 'lecturer';
    $prefix = $role === 'lecturer' ? 'lecturer.lms' : 'student.lms';
    $tabs = $role === 'lecturer'
        ? [
            'overview' => ['label' => 'Overview', 'route' => route('lecturer.lms.show', $offering)],
            'outline' => ['label' => 'Outline', 'route' => route('lecturer.lms.outline', $offering)],
            'content' => ['label' => 'Content', 'route' => route('lecturer.lms.content', $offering)],
            'assignments' => ['label' => 'Assignments', 'route' => route('lecturer.lms.assignments', $offering)],
            'announcements' => ['label' => 'Announcements', 'route' => route('lecturer.lms.announcements', $offering)],
            'discussions' => ['label' => 'Discussions', 'route' => route('lecturer.lms.discussions', $offering)],
            'analytics' => ['label' => 'Analytics', 'route' => route('lecturer.lms.analytics', $offering)],
        ]
        : [
            'overview' => ['label' => 'Overview', 'route' => route('student.lms.show', $offering)],
            'content' => ['label' => 'Content', 'route' => route('student.lms.content', $offering)],
            'assignments' => ['label' => 'Assignments', 'route' => route('student.lms.assignments', $offering)],
            'discussions' => ['label' => 'Discussions', 'route' => route('student.lms.discussions', $offering)],
        ];
@endphp

<div class="bg-white rounded-lg shadow mb-6">
    <div class="border-b border-gray-100 px-5 py-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#8cc63f]">Learning Management System</p>
        <h3 class="text-lg font-semibold text-[#0f2744] mt-1">{{ $offering->course->code }} — {{ $offering->course->title }}</h3>
        <p class="text-sm text-gray-500 mt-1">{{ $offering->academic_year }} · Semester {{ $offering->semester }} · {{ $offering->lecturer?->name }}</p>
    </div>
    <nav class="flex flex-wrap gap-1 px-3 py-2 border-b border-gray-100 text-sm">
        @foreach($tabs as $key => $tab)
            <a href="{{ $tab['route'] }}"
               class="rounded-lg px-3 py-2 font-medium {{ $active === $key ? 'bg-[#0f2744] text-white' : 'text-[#0f2744] hover:bg-gray-50' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
