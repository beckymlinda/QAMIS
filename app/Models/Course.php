<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'programme_id', 'code', 'title',
        'credit_hours', 'year_level', 'semester_number', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_hours' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function displayLabel(): string
    {
        return "{$this->code} — {$this->title}";
    }
}
