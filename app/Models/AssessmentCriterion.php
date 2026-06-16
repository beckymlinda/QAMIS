<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCriterion extends Model
{
    protected $fillable = [
        'assessment_section_id', 'parent_criterion_id', 'standard_clause_id',
        'sequence_no', 'title', 'description', 'is_mandatory', 'minimum_score', 'weight', 'source_text',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'weight' => 'decimal:2',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentSection::class, 'assessment_section_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_criterion_id');
    }
}
