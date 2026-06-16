<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AssessmentResponse extends Model
{
    protected $fillable = [
        'assessment_id', 'assessment_criterion_id', 'score', 'is_available',
        'comments', 'reviewer_observations', 'strengths', 'areas_for_improvement',
        'recommendations', 'scored_by', 'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'scored_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(AssessmentCriterion::class, 'assessment_criterion_id');
    }

    public function evidenceVersions(): BelongsToMany
    {
        return $this->belongsToMany(
            EvidenceDocumentVersion::class,
            'assessment_response_evidence',
            'assessment_response_id',
            'evidence_document_version_id'
        );
    }
}
