<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Institution extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'acronym', 'establishment_year', 'registered_at', 'accredited_at',
        'web_address', 'thematic_focus', 'programme_levels', 'status',
    ];

    protected function casts(): array
    {
        return [
            'thematic_focus' => 'array',
            'programme_levels' => 'array',
            'registered_at' => 'date',
            'accredited_at' => 'date',
        ];
    }

    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(InstitutionProfile::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(InstitutionContact::class);
    }

    public function orgUnits(): HasMany
    {
        return $this->hasMany(OrgUnit::class);
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function governanceMembers(): HasMany
    {
        return $this->hasMany(GovernanceMember::class);
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class);
    }

    public function studentEnrolments(): HasMany
    {
        return $this->hasMany(StudentEnrolment::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
