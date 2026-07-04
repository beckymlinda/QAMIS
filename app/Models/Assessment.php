<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assessment extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'programme_id', 'assessment_template_id', 'assessment_type',
        'title', 'period_start', 'period_end', 'status', 'assessor_names',
        'narrative_recommendations',
        'submitted_at', 'reviewed_at', 'approved_at', 'approved_by', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'status' => AssessmentStatus::class,
            'narrative_recommendations' => 'array',
        ];
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [AssessmentStatus::Draft, AssessmentStatus::Submitted, AssessmentStatus::Reviewed], true);
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, [AssessmentStatus::Approved, AssessmentStatus::Locked], true);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'assessment_template_id');
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }

    public function sectionSummaries(): HasMany
    {
        return $this->hasMany(AssessmentSectionSummary::class);
    }

    public function complianceResult(): HasOne
    {
        return $this->hasOne(ComplianceResult::class);
    }

    public function workflowHistory(): HasMany
    {
        return $this->hasMany(AssessmentWorkflowHistory::class);
    }
}
