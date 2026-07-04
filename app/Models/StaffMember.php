<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'user_id', 'org_unit_id', 'programme_id', 'type', 'name', 'gender',
        'qualification', 'awarding_institution', 'qualification_year', 'rank',
        'designation', 'employment_type', 'courses_taught', 'experience_years',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class, 'staff_member_id');
    }
}
