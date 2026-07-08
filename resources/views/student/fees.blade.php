<x-app-layout>
    <div class="min-h-full bg-gradient-to-b from-slate-50 via-gray-50 to-gray-100/80">
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-[#0f2744] sm:text-2xl">Tuition &amp; Fees</h1>
                    <p class="text-xs text-gray-500">{{ $student->programme?->name }}</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="text-sm font-semibold text-[#0f2744]">← Portal home</a>
            </div>

            @include('partials.alerts')

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-xs uppercase text-gray-500">Total programme fees</p>
                    <p class="mt-1 text-2xl font-bold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney($summary['total_due']) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-xs uppercase text-gray-500">Total paid</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">{{ \App\Services\StudentFeesService::formatMoney($summary['total_paid']) }}</p>
                    @if($summary['application_fee_credit'] > 0)
                        <p class="mt-1 text-xs text-gray-500">Includes {{ \App\Services\StudentFeesService::formatMoney($summary['application_fee_credit']) }} application fee credit</p>
                    @endif
                </div>
                <div class="rounded-2xl p-5 shadow-sm ring-1 ring-[#0f2744]/20" style="background:color-mix(in srgb, var(--brand-primary, #0f2744) 8%, white)">
                    <p class="text-xs uppercase text-gray-600">Current balance</p>
                    <p class="mt-1 text-2xl font-bold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney($summary['balance']) }}</p>
                    @if($summary['pending_payments_total'] > 0)
                        <p class="mt-1 text-xs text-amber-700">{{ \App\Services\StudentFeesService::formatMoney($summary['pending_payments_total']) }} awaiting approval</p>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-sm font-bold text-[#0f2744]">Fee breakdown</h2>
                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr><th class="px-4 py-3">Item</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($summary['charges'] as $charge)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $charge['label'] }}</td>
                                    <td class="px-4 py-3 text-right">{{ \App\Services\StudentFeesService::formatMoney($charge['amount']) }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        @if($charge['paid'])<span class="text-green-700">Paid</span>@elseif($charge['note']){{ $charge['note'] }}@else<span class="text-gray-400">Due</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100" x-data="{ amount: '{{ old('amount', '') }}', balance: {{ $summary['balance'] }}, projected() { const a = parseFloat(this.amount) || 0; return Math.max(0, this.balance - a).toFixed(0); } }">
                    <h2 class="text-sm font-bold text-[#0f2744]">Submit payment receipt</h2>
                    <p class="mt-1 text-xs text-gray-500">Upload your bank deposit slip or mobile money receipt. Finance will verify before your balance is updated.</p>
                    <form method="POST" action="{{ route('student.fees.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium">Amount paid (MK) *</label>
                            <input type="number" name="amount" x-model="amount" min="1" step="1" required class="mt-1 w-full rounded-xl border-gray-300">
                            <p class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-900" x-show="amount > 0">
                                With this payment, your new balance will be <strong x-text="'MK ' + Number(projected()).toLocaleString()"></strong>
                                <span class="block mt-1 text-amber-700">Status: Awaiting approval</span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Payment reference</label>
                            <input name="payment_reference" value="{{ old('payment_reference') }}" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Bank / mobile money reference">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Receipt file *</label>
                            <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required class="mt-1 w-full text-sm">
                        </div>
                        <button type="submit" class="rounded-xl px-6 py-2.5 text-sm font-bold text-white" style="background:var(--brand-primary, #0f2744)">Submit for approval</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h2 class="text-sm font-bold text-[#0f2744]">Payment history</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($summary['payments'] as $payment)
                            <div class="rounded-xl border border-gray-100 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-[#0f2744]">{{ \App\Services\StudentFeesService::formatMoney((float) $payment->amount) }}</p>
                                        <p class="text-xs text-gray-500">{{ $payment->submitted_at?->format('d M Y, H:i') }} · {{ $payment->payment_reference ?? 'No reference' }}</p>
                                        @if($payment->balance_after !== null)
                                            <p class="mt-1 text-xs text-gray-600">Projected balance: {{ \App\Services\StudentFeesService::formatMoney((float) $payment->balance_after) }}</p>
                                        @endif
                                    </div>
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $payment->status->badgeClasses() }}">{{ $payment->status->label() }}</span>
                                </div>
                                @if($payment->receipt_path)
                                    <a href="{{ route('student.fees.receipt', $payment) }}" target="_blank" class="mt-2 inline-flex text-xs font-semibold text-[#0f2744] underline">View receipt</a>
                                @endif
                                @if($payment->admin_notes)
                                    <p class="mt-2 text-xs text-gray-600">{{ $payment->admin_notes }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No payments submitted yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
