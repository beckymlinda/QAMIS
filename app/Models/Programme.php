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
        'nche_accreditation_status', 'professional_body',
        'curriculum_developed_at', 'curriculum_reviewed_at', 'accredited_at',
        'timetable_generation_pass',
    ];

    protected function casts(): array
    {
        return [
            'delivery_modes' => 'array',
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
}
