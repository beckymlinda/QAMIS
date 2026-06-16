<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentTemplate extends Model
{
    protected $fillable = ['standard_version_id', 'type', 'name', 'version', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(StandardVersion::class, 'standard_version_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(AssessmentSection::class)->orderBy('sort_order');
    }
}
