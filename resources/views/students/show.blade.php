<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">{{ $student->fullName() }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $student->student_number }} · {{ $student->programme?->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('students.courses', $student) }}" class="inline-flex rounded-lg bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744]">View courses</a>
                @can('update', $student)
                    <a href="{{ route('students.edit', $student) }}" class="inline-flex rounded-lg border border-[#0f2744] px-4 py-2 text-sm font-medium text-[#0f2744] hover:bg-[#0f2744]/5">Edit</a>
                @endcan
                <a href="{{ route('students.index', ['programme_id' => $student->programme_id]) }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">← All students</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ tab: '{{ $tab }}' }">
        @include('partials.alerts')

        <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-1">
            @foreach(['personal' => 'Personal details', 'grades' => 'Grades', 'fees' => 'Fees & payments'] as $key => $label)
                <a href="{{ route('students.show', [$student, 'tab' => $key]) }}"
                   class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition {{ $tab === $key ? 'bg-white text-[#0f2744] ring-1 ring-gray-200 ring-b-white -mb-px' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($tab === 'personal')
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <dl class="divide-y divide-gray-100 text-sm">
                    @foreach([
                        'Full name' => $student->fullName(),
                        'Student number' => $student->student_number,
                        'Phone' => $student->phone ?? '—',
                        'Email (portal login)' => $student->email,
                        'Programme' => $student->programme?->name ?? '—',
                        'Year of study' => 'Year '.$student->year_of_study,
                        'Status' => ucfirst($student->status ?? 'active'),
                        'Portal account' => $student->user ? 'Active' : 'No login linked',
                    ] as $label => $value)
                        <div class="grid sm:grid-cols-3 gap-2 px-6 py-4">
                            <dt class="text-gray-500">{{ $label }}</dt>
                            <dd class="sm:col-span-2 font-medium text-[#0f2744]">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
            @can('delete', $student)
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Remove this student and their portal account?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-red-700">Remove student</button>
                    </form>
                </div>
            @endcan
        @elseif($tab === 'grades')
            @include('partials.exam-results-panel', [
                'results' => $results,
                'periods' => $periods,
                'academicYear' => $academicYear,
                'semester' => $semester,
                'semesterGpa' => $semesterGpa,
                'cumulativeGpa' => $cumulativeGpa,
                'formAction' => route('students.show', $student),
            ])
        @else
            @php $summary = $feeSummary; @endphp
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100"><p class="text-xs text-gray-500">Total fees</p><p class="mt-1 text-xl font-bold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney($summary['total_due']) }}</p></div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100"><p class="text-xs text-gray-500">Tuition</p><p class="mt-1 text-xl font-bold">{{ \App\Services\StudentFeesService::formatMoney((float) ($summary['programme']?->tuition_fee ?? 0)) }}</p></div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100"><p class="text-xs text-gray-500">Total paid</p><p class="mt-1 text-xl font-bold text-green-700">{{ \App\Services\StudentFeesService::formatMoney($summary['total_paid']) }}</p></div>
                <div class="rounded-xl bg-[#0f2744]/5 p-4 ring-1 ring-[#0f2744]/15"><p class="text-xs text-gray-600">Balance</p><p class="mt-1 text-xl font-bold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney($summary['balance']) }}</p></div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-bold text-[#0f2744]">Programme fee schedule</h3>
                <table class="mt-4 min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-2">Fee type</th><th class="px-4 py-2 text-right">Amount</th><th class="px-4 py-2">Notes</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($summary['charges'] as $charge)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $charge['label'] }}</td>
                                <td class="px-4 py-3 text-right">{{ \App\Services\StudentFeesService::formatMoney($charge['amount']) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">@if($charge['paid'])<span class="text-green-700">Paid</span>@else{{ $charge['note'] ?? 'Outstanding' }}@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-bold text-[#0f2744]">Submitted receipts</h3>
                <div class="mt-4 space-y-4">
                    @forelse($summary['payments'] as $payment)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney((float) $payment->amount) }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->submitted_at?->format('d M Y, H:i') }} · Ref: {{ $payment->payment_reference ?? '—' }}</p>
                                    @if($payment->balance_after !== null)
                                        <p class="mt-1 text-xs text-gray-600">Projected balance after payment: {{ \App\Services\StudentFeesService::formatMoney((float) $payment->balance_after) }}</p>
                                    @endif
                                </div>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $payment->status->badgeClasses() }}">{{ $payment->status->label() }}</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($payment->receipt_path)
                                    <a href="{{ route('students.fee-payments.receipt', [$student, $payment]) }}" target="_blank" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold">Preview receipt</a>
                                @endif
                                @if($payment->status === \App\Enums\FeePaymentStatus::Pending)
                                    @can('update', $student)
                                        <form method="POST" action="{{ route('students.fee-payments.approve', [$student, $payment]) }}" class="inline">@csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('students.fee-payments.reject', [$student, $payment]) }}" class="inline" onsubmit="return confirm('Reject this payment?');">@csrf
                                            <button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">Reject</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No payment receipts uploaded yet.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
