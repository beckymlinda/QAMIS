<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationPeriod extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'academic_year', 'semester', 'title',
        'opens_at', 'closes_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function teachingEvaluations(): HasMany
    {
        return $this->hasMany(TeachingEvaluation::class);
    }

    public function isOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        return $now->gte($this->opens_at) && $now->lte($this->closes_at);
    }
}
