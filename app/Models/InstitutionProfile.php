<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionProfile extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'vision', 'mission', 'core_values',
        'strategic_plan_summary', 'background_narrative', 'swot_analysis',
        'executive_summary', 'abbreviations_acronyms', 'introduction_approach',
        'assessment_team_composition', 'core_function', 'policies_procedures_summary',
    ];

    protected function casts(): array
    {
        return ['swot_analysis' => 'array'];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
