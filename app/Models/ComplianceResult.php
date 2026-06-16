<?php

namespace App\Models;

use App\Enums\AccreditationRecommendation;
use App\Enums\ComplianceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceResult extends Model
{
    protected $fillable = [
        'assessment_id', 'overall_average', 'compliance_status',
        'accreditation_recommendation', 'mandatory_failures', 'risk_level',
        'missing_mandatory_evidence', 'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_average' => 'decimal:2',
            'compliance_status' => ComplianceStatus::class,
            'accreditation_recommendation' => AccreditationRecommendation::class,
            'mandatory_failures' => 'array',
            'missing_mandatory_evidence' => 'boolean',
            'computed_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
