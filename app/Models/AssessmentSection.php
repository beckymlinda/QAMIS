<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSection extends Model
{
    protected $fillable = [
        'assessment_template_id', 'standard_area_id', 'code', 'title',
        'minimum_standard_ref', 'divisor', 'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'assessment_template_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(AssessmentCriterion::class)->orderBy('sequence_no');
    }
}
