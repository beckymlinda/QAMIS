<?php

namespace App\Models;

use App\Enums\ProgrammeApplicationStatus;
use App\Support\ApplicationDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeApplication extends Model
{
    protected $fillable = [
        'institution_id',
        'programme_id',
        'user_id',
        'application_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'nationality',
        'certificate_grades',
        'status',
        'payment_reference',
        'payment_verified_at',
        'payment_verified_by',
        'admin_notes',
        'reviewed_by',
        'submitted_at',
        'decision_at',
        'enrolled_at',
        'enrolled_student_id',
        'id_document_path',
        'certificates_path',
        'results_path',
        'photo_path',
        'payment_proof_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'payment_verified_at' => 'datetime',
            'submitted_at' => 'datetime',
            'decision_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'status' => ProgrammeApplicationStatus::class,
            'certificate_grades' => 'array',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function enrolledStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'enrolled_student_id');
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_verified_at !== null;
    }

    public function canBeEnrolled(): bool
    {
        return $this->enrolled_student_id === null
            && $this->status === ProgrammeApplicationStatus::Approved;
    }

    public function needsStudentRecord(): bool
    {
        return $this->enrolled_student_id === null
            && $this->status !== ProgrammeApplicationStatus::Rejected;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            ProgrammeApplicationStatus::Rejected,
            ProgrammeApplicationStatus::Approved,
            ProgrammeApplicationStatus::Enrolled,
            ProgrammeApplicationStatus::WaitingList,
        ], true);
    }

    public function canBeEditedByApplicant(): bool
    {
        $this->loadMissing('programme');

        return $this->programme->isOpenForApplications() && ! $this->isTerminal();
    }

    public static function activeForUser(int $userId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', [
                ProgrammeApplicationStatus::Rejected,
                ProgrammeApplicationStatus::Enrolled,
            ])
            ->latest('submitted_at')
            ->first();
    }

    /** @return array<string, string|null> */
    public function documentPaths(): array
    {
        return [
            'certificates' => $this->certificates_path,
            'results' => $this->results_path,
            'payment_proof' => $this->payment_proof_path,
            'id_document' => $this->id_document_path,
            'photo' => $this->photo_path,
        ];
    }

    public function hasRequiredDocuments(): bool
    {
        foreach (ApplicationDocuments::requiredFields() as $field) {
            if (! ApplicationDocuments::pathFor($this, $field)) {
                return false;
            }
        }

        return true;
    }
}
