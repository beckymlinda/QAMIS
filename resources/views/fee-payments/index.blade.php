<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">Finance</p>
            <h2 class="text-xl font-bold text-brand-primary">Fee payments</h2>
            <p class="text-sm text-gray-500">Track expected fees, outstanding balances, and approved income across students.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        <form method="GET" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="text-xs font-medium text-gray-600">Payment status</label>
                    <select name="payment_status" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        @foreach(['all' => 'All students', 'paid' => 'Fully paid', 'partial' => 'Partially paid', 'unpaid' => 'Not paid'] as $val => $label)
                            <option value="{{ $val }}" @selected($filters['payment_status'] === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Programme</label>
                    <select name="programme_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        <option value="">All programmes</option>
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}" @selected($filters['programme_id'] == $programme->id)>{{ $programme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Year of study</label>
                    <select name="year_of_study" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        <option value="">All years</option>
                        @foreach($yearsOfStudy as $year)
                            <option value="{{ $year }}" @selected($filters['year_of_study'] == $year)>Year {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Student</label>
                    <select name="student_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        <option value="">All students</option>
                        @foreach($allStudents as $student)
                            <option value="{{ $student->id }}" @selected($filters['student_id'] == $student->id)>{{ $student->fullName() }} ({{ $student->student_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Income period</label>
                    <select name="income_period" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        @foreach(['all' => 'All time', 'week' => 'This week', 'month' => 'This month', 'semester' => 'This semester'] as $val => $label)
                            <option value="{{ $val }}" @selected($filters['income_period'] === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="rounded-xl px-5 py-2 text-sm font-semibold text-white" style="background:var(--brand-primary, #0f2744)">Apply filters</button>
                <a href="{{ route('fee-payments.index') }}" class="rounded-xl border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs uppercase text-gray-500">Total expected fees</p>
                <p class="mt-1 text-2xl font-bold text-brand-primary">{{ \App\Services\StudentFeesService::formatMoney($report['total_expected']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Sum of programme fees for filtered students</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs uppercase text-gray-500">Total outstanding</p>
                <p class="mt-1 text-2xl font-bold text-red-700">{{ \App\Services\StudentFeesService::formatMoney($report['total_outstanding']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs uppercase text-gray-500">Total paid (filtered)</p>
                <p class="mt-1 text-2xl font-bold text-green-700">{{ \App\Services\StudentFeesService::formatMoney($report['total_paid']) }}</p>
            </div>
            <div class="rounded-2xl p-5 shadow-sm ring-1 ring-blue-200" style="background:color-mix(in srgb, var(--brand-primary, #0f2744) 6%, white)">
                <p class="text-xs uppercase text-gray-600">Income · {{ \App\Services\StudentFeesService::incomePeriodLabel($report['income_period']) }}</p>
                <p class="mt-1 text-2xl font-bold text-brand-primary">{{ \App\Services\StudentFeesService::formatMoney($report['income_collected']) }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $report['pending_approvals'] }} receipt(s) awaiting approval</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="font-bold text-brand-primary">Students ({{ $report['rows']->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Programme</th>
                            <th class="px-4 py-3">Year</th>
                            <th class="px-4 py-3 text-right">Expected</th>
                            <th class="px-4 py-3 text-right">Paid</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($report['rows'] as $row)
                            @php $s = $row['summary']; $student = $row['student']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $student->fullName() }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->student_number }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $student->programme?->name ?? '—' }}</td>
                                <td class="px-4 py-3">Year {{ $student->year_of_study }}</td>
                                <td class="px-4 py-3 text-right">{{ \App\Services\StudentFeesService::formatMoney($s['total_due']) }}</td>
                                <td class="px-4 py-3 text-right text-green-700">{{ \App\Services\StudentFeesService::formatMoney($s['total_paid']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ \App\Services\StudentFeesService::formatMoney($s['balance']) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($s['payment_status']) {
                                            'paid' => 'bg-green-100 text-green-800 ring-green-200',
                                            'partial' => 'bg-amber-100 text-amber-900 ring-amber-200',
                                            default => 'bg-red-100 text-red-800 ring-red-200',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $badge }}">{{ \App\Services\StudentFeesService::paymentStatusLabel($s['payment_status']) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('students.show', [$student, 'tab' => 'fees']) }}" class="font-semibold text-brand-primary">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center text-gray-500">No students match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
