<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDashboardCache extends Model
{
    public $timestamps = false;

    protected $table = 'compliance_dashboard_cache';

    protected $fillable = [
        'institution_id', 'scope_type', 'scope_id', 'overall_compliance_pct',
        'by_standard', 'trend_data', 'outstanding_actions', 'evidence_completeness_pct', 'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'overall_compliance_pct' => 'decimal:2',
            'by_standard' => 'array',
            'trend_data' => 'array',
            'evidence_completeness_pct' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
