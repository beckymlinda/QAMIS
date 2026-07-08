<?php

namespace App\Services;

use App\Enums\FeePaymentStatus;
use App\Models\ProgrammeApplication;
use App\Models\Student;
use App\Models\StudentFeePayment;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class StudentFeesService
{
    /** @return array<string, mixed> */
    public function summary(Student $student): array
    {
        $student->loadMissing('programme');
        $programme = $student->programme;

        $application = ProgrammeApplication::query()
            ->where('enrolled_student_id', $student->id)
            ->first();

        $applicationFee = (float) ($programme?->application_fee ?? 0);
        $registrationFee = (float) ($programme?->registration_fee ?? 0);
        $tuitionFee = (float) ($programme?->tuition_fee ?? 0);
        $otherFees = (float) ($programme?->other_fees ?? 0);

        $applicationPaid = $application?->isPaymentVerified() ?? false;
        $applicationCredit = ($applicationPaid && $applicationFee > 0) ? $applicationFee : 0;

        $charges = collect([
            [
                'key' => 'application_fee',
                'label' => 'Application fee',
                'amount' => $applicationFee,
                'paid' => $applicationPaid,
                'note' => $applicationPaid ? 'Verified during admission' : ($application ? 'Pending verification' : 'Not on record'),
            ],
            [
                'key' => 'registration_fee',
                'label' => 'Registration fee',
                'amount' => $registrationFee,
                'paid' => false,
                'note' => null,
            ],
            [
                'key' => 'tuition_fee',
                'label' => 'Programme tuition',
                'amount' => $tuitionFee,
                'paid' => false,
                'note' => null,
            ],
            [
                'key' => 'other_fees',
                'label' => 'Other fees',
                'amount' => $otherFees,
                'paid' => false,
                'note' => null,
            ],
        ])->filter(fn ($line) => $line['amount'] > 0);

        $totalDue = (float) $charges->sum('amount');

        $payments = $this->payments($student);
        $approvedReceiptTotal = (float) $payments->where('status', FeePaymentStatus::Approved)->sum('amount');
        $pendingTotal = (float) $payments->where('status', FeePaymentStatus::Pending)->sum('amount');
        $totalPaid = round($applicationCredit + $approvedReceiptTotal, 2);
        $balance = max(0, round($totalDue - $totalPaid, 2));

        return [
            'programme' => $programme,
            'application' => $application,
            'charges' => $charges,
            'total_due' => round($totalDue, 2),
            'application_fee_credit' => round($applicationCredit, 2),
            'approved_receipts_total' => round($approvedReceiptTotal, 2),
            'approved_payments_total' => $totalPaid,
            'total_paid' => $totalPaid,
            'pending_payments_total' => round($pendingTotal, 2),
            'balance' => $balance,
            'payment_status' => $this->paymentStatus($totalPaid, $balance),
            'payments' => $payments,
        ];
    }

    public function paymentStatus(float $totalPaid, float $balance): string
    {
        if ($balance <= 0 && $totalPaid > 0) {
            return 'paid';
        }

        if ($totalPaid <= 0) {
            return 'unpaid';
        }

        return 'partial';
    }

    public function projectedBalance(Student $student, float $amount): float
    {
        $summary = $this->summary($student);

        return max(0, round($summary['balance'] - $amount, 2));
    }

    public function payments(Student $student): Collection
    {
        return StudentFeePayment::query()
            ->where('student_id', $student->id)
            ->latest('submitted_at')
            ->get();
    }

    /**
     * @param  array{programme_id?: ?int, year_of_study?: ?int, payment_status?: ?string, income_period?: ?string}  $filters
     * @return array<string, mixed>
     */
    public function institutionReport(int $institutionId, array $filters = []): array
    {
        $students = Student::query()
            ->where('institution_id', $institutionId)
            ->with('programme')
            ->when($filters['programme_id'] ?? null, fn ($q, $id) => $q->where('programme_id', $id))
            ->when($filters['year_of_study'] ?? null, fn ($q, $year) => $q->where('year_of_study', $year))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = collect();
        $totalExpected = 0.0;
        $totalOutstanding = 0.0;
        $totalPaidAll = 0.0;

        foreach ($students as $student) {
            $summary = $this->summary($student);

            if (($filters['payment_status'] ?? null) && $filters['payment_status'] !== 'all') {
                if ($summary['payment_status'] !== $filters['payment_status']) {
                    continue;
                }
            }

            $totalExpected += $summary['total_due'];
            $totalOutstanding += $summary['balance'];
            $totalPaidAll += $summary['total_paid'];

            $rows->push([
                'student' => $student,
                'summary' => $summary,
            ]);
        }

        $incomeStart = self::incomePeriodStart($filters['income_period'] ?? 'all');
        $incomeCollected = $this->incomeCollected($institutionId, $incomeStart, $filters);

        $pendingApprovals = StudentFeePayment::query()
            ->where('institution_id', $institutionId)
            ->where('status', FeePaymentStatus::Pending)
            ->count();

        return [
            'rows' => $rows,
            'total_expected' => round($totalExpected, 2),
            'total_outstanding' => round($totalOutstanding, 2),
            'total_paid' => round($totalPaidAll, 2),
            'income_collected' => round($incomeCollected, 2),
            'pending_approvals' => $pendingApprovals,
            'income_period' => $filters['income_period'] ?? 'all',
        ];
    }

    /** @param  array{programme_id?: ?int}  $filters */
    protected function incomeCollected(int $institutionId, ?Carbon $since, array $filters): float
    {
        $receiptIncome = StudentFeePayment::query()
            ->where('institution_id', $institutionId)
            ->where('status', FeePaymentStatus::Approved)
            ->when($since, fn ($q) => $q->where('reviewed_at', '>=', $since))
            ->when($filters['programme_id'] ?? null, function ($q, $programmeId) {
                $q->whereHas('student', fn ($s) => $s->where('programme_id', $programmeId));
            })
            ->sum('amount');

        $applicationIncome = ProgrammeApplication::query()
            ->where('institution_id', $institutionId)
            ->whereNotNull('payment_verified_at')
            ->when($since, fn ($q) => $q->where('payment_verified_at', '>=', $since))
            ->when($filters['programme_id'] ?? null, fn ($q, $id) => $q->where('programme_id', $id))
            ->with('programme')
            ->get()
            ->sum(fn ($app) => (float) ($app->programme?->application_fee ?? 0));

        return (float) $receiptIncome + (float) $applicationIncome;
    }

    public static function incomePeriodStart(?string $period): ?Carbon
    {
        return match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'semester' => now()->month <= 6
                ? now()->copy()->month(1)->day(1)->startOfDay()
                : now()->copy()->month(7)->day(1)->startOfDay(),
            default => null,
        };
    }

    public static function incomePeriodLabel(?string $period): string
    {
        return match ($period) {
            'week' => 'This week',
            'month' => 'This month',
            'semester' => 'This semester',
            default => 'All time',
        };
    }

    public function storePayment(Student $student, array $data, UploadedFile $receipt): StudentFeePayment
    {
        $amount = (float) $data['amount'];
        $balanceAfter = $this->projectedBalance($student, $amount);

        $path = $receipt->store(
            'institutions/'.$student->institution_id.'/students/'.$student->id.'/fee-receipts',
            'local'
        );

        return StudentFeePayment::create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'payment_reference' => $data['payment_reference'] ?? null,
            'receipt_path' => $path,
            'status' => FeePaymentStatus::Pending,
            'submitted_at' => now(),
        ]);
    }

    public function approve(StudentFeePayment $payment, ?string $notes = null): void
    {
        $payment->update([
            'status' => FeePaymentStatus::Approved,
            'admin_notes' => $notes ?? $payment->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function reject(StudentFeePayment $payment, ?string $notes = null): void
    {
        $payment->update([
            'status' => FeePaymentStatus::Rejected,
            'admin_notes' => $notes ?? $payment->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public static function formatMoney(?float $amount): string
    {
        return $amount !== null ? 'MK '.number_format($amount, 0) : '—';
    }

    public static function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Fully paid',
            'partial' => 'Partially paid',
            'unpaid' => 'Not paid',
            default => ucfirst($status),
        };
    }
}
