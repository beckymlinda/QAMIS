<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentCriterionRubricLevel extends Model
{
    protected $fillable = [
        'assessment_criterion_id',
        'score',
        'level_label',
        'descriptor',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(AssessmentCriterion::class, 'assessment_criterion_id');
    }
}
