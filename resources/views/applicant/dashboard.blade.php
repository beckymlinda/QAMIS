<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">Applicant portal</p>
            <h2 class="text-xl font-bold text-brand-primary">My applications</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs uppercase tracking-wide text-gray-500">Applications</p>
                <p class="mt-1 text-3xl font-bold text-brand-primary">{{ $applications->count() }}</p>
            </div>
            @if($activeApplication)
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:col-span-2">
                    <p class="text-sm font-semibold text-blue-900">Active application: {{ $activeApplication->application_number }}</p>
                    <p class="mt-1 text-sm text-blue-800">{{ $activeApplication->programme->name }} · {{ $activeApplication->status->label() }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('applicant.applications.show', $activeApplication) }}" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-brand-primary ring-1 ring-blue-200">View details</a>
                        @if($activeApplication->canBeEditedByApplicant())
                            <a href="{{ route('applicant.applications.edit', $activeApplication) }}" class="rounded-lg bg-brand-secondary px-4 py-2 text-xs font-bold text-brand-primary">Edit application</a>
                        @endif
                    </div>
                </div>
            @elseif($website)
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-5 sm:col-span-2">
                    <p class="text-sm font-semibold text-gray-900">Ready to apply?</p>
                    <p class="mt-1 text-sm text-gray-500">Start your admission application for {{ $website->displayName() }}.</p>
                    <a href="{{ route('applicant.apply.create', $website->slug) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-brand-secondary px-5 py-2.5 text-sm font-bold text-brand-primary">
                        <i class="bi bi-file-earmark-plus"></i> Start application
                    </a>
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="font-bold text-gray-900">Application history</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Application #</th>
                            <th class="px-4 py-3">Programme</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $application->application_number }}</td>
                                <td class="px-4 py-3">{{ $application->programme->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $application->submitted_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('applicant.applications.show', $application) }}" class="font-semibold text-brand-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No applications yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
