<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrolment extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'programme_id', 'qualification_type', 'delivery_mode',
        'male_count', 'female_count', 'citizenship', 'has_disability', 'reporting_year',
    ];

    protected function casts(): array
    {
        return ['has_disability' => 'boolean'];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}
