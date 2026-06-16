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
