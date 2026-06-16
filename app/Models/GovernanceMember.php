<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceMember extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'body_type', 'name', 'gender', 'qualification',
        'awarding_institution', 'designation', 'specialization', 'experience_years', 'sort_order',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
