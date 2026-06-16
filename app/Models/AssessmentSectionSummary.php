<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSectionSummary extends Model
{
    protected $fillable = [
        'assessment_id', 'assessment_section_id', 'total_score', 'aggregate_score',
        'divisor', 'strengths', 'areas_for_improvement', 'recommendations',
    ];

    protected function casts(): array
    {
        return ['aggregate_score' => 'decimal:2'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentSection::class, 'assessment_section_id');
    }
}
