<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-brand-primary">Applications</h2>
        </div>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
        @include('partials.alerts')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach(['total' => 'Total', 'submitted' => 'Submitted', 'under_review' => 'Under review', 'approved' => 'Approved', 'pending_payment' => 'Pending payment'] as $key => $label)
                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <p class="text-xs uppercase text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-brand-primary">{{ $stats[$key] }}</p>
                </div>
            @endforeach
        </div>
        <form method="GET" class="flex flex-wrap gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <select name="status" class="rounded-xl border-gray-300 text-sm"><option value="">All statuses</option>@foreach($statuses as $val => $label)<option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>@endforeach</select>
            <select name="programme_id" class="rounded-xl border-gray-300 text-sm"><option value="">All programmes</option>@foreach($programmes as $p)<option value="{{ $p->id }}" @selected(request('programme_id')==$p->id)>{{ $p->name }}</option>@endforeach</select>
            <button class="rounded-xl bg-brand-primary px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-3">Application</th><th class="px-4 py-3">Applicant</th><th class="px-4 py-3">Programme</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $application)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $application->application_number }}</td>
                            <td class="px-4 py-3">{{ $application->fullName() }}<br><span class="text-xs text-gray-500">{{ $application->email }}</span></td>
                            <td class="px-4 py-3">{{ $application->programme->name }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span></td>
                            <td class="px-4 py-3">{{ $application->isPaymentVerified() ? 'Verified' : 'Pending' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('applications.show', $application) }}" class="font-semibold text-brand-primary">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->links() }}
    </div>
</x-app-layout>
