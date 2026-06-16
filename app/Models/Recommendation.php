<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recommendation extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'assessment_id', 'assessment_section_id', 'assessment_criterion_id',
        'prior_assessment_id', 'description', 'source', 'status', 'progress_notes',
    ];

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
