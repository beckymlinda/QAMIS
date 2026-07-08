<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'org_unit_id', 'name', 'level', 'delivery_modes',
        'total_credit_hours', 'duration', 'description',
        'tuition_fee', 'application_fee', 'registration_fee', 'other_fees',
        'entry_requirements', 'required_grades', 'max_intake',
        'application_closing_date', 'applications_open',
        'nche_accreditation_status', 'professional_body',
        'curriculum_developed_at', 'curriculum_reviewed_at', 'accredited_at',
        'timetable_generation_pass',
    ];

    protected function casts(): array
    {
        return [
            'delivery_modes' => 'array',
            'total_credit_hours' => 'decimal:1',
            'tuition_fee' => 'decimal:2',
            'application_fee' => 'decimal:2',
            'registration_fee' => 'decimal:2',
            'other_fees' => 'decimal:2',
            'applications_open' => 'boolean',
            'application_closing_date' => 'date',
            'curriculum_developed_at' => 'date',
            'curriculum_reviewed_at' => 'date',
            'accredited_at' => 'date',
        ];
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ProgrammeApplication::class);
    }

    public function isOpenForApplications(): bool
    {
        if (! $this->applications_open) {
            return false;
        }

        if ($this->application_closing_date === null) {
            return true;
        }

        return now()->startOfDay()->lte($this->application_closing_date);
    }

    public function formattedFee(?float $amount): string
    {
        return $amount !== null ? 'MK '.number_format($amount, 0) : '—';
    }
}
