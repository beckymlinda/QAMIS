<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardVersion extends Model
{
    protected $fillable = ['name', 'code', 'effective_from', 'effective_to', 'is_active'];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function areas(): HasMany
    {
        return $this->hasMany(StandardArea::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(AssessmentTemplate::class);
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(ScoringRubric::class);
    }
}
