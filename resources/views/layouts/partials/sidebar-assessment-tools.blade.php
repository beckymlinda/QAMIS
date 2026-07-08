@php
    $assessmentToolsActive = request()->routeIs('assessments.*')
        || request()->routeIs('institutions.report-data.*')
        || request()->routeIs('institution-data')
        || request()->routeIs('evidence.*')
        || request()->routeIs('reports.*');
@endphp
<x-sidebar-dropdown label="Assessment Tools" :active="$assessmentToolsActive">
    <x-slot:icon>
        <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
    </x-slot:icon>
    @can('assessment.create')
        <x-sidebar-sublink :href="route('assessments.programme.index')" :active="request()->routeIs('assessments.programme.*') || (request()->routeIs('assessments.create') && request('type') === 'programme')" @click="sidebarOpen = false">Programme Assessment</x-sidebar-sublink>
        <x-sidebar-sublink :href="route('assessments.institution.index')" :active="request()->routeIs('assessments.institution.*') || (request()->routeIs('assessments.create') && request('type', 'institutional') === 'institutional') || (request()->routeIs('assessments.index'))" @click="sidebarOpen = false">Institution Assessment</x-sidebar-sublink>
    @endcan
    <x-sidebar-sublink :href="route('institution-data')" :active="request()->routeIs('institutions.report-data.*') || request()->routeIs('institution-data')" @click="sidebarOpen = false">Report Data</x-sidebar-sublink>
    @can('evidence.upload')
        <x-sidebar-sublink :href="route('evidence.index')" :active="request()->routeIs('evidence.*')" @click="sidebarOpen = false">Evidence</x-sidebar-sublink>
    @endcan
    @can('report.view')
        <x-sidebar-sublink :href="route('reports.index')" :active="request()->routeIs('reports.*')" @click="sidebarOpen = false">Generate Reports</x-sidebar-sublink>
    @endcan
</x-sidebar-dropdown>
