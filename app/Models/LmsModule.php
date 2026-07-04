<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsModule extends Model
{
    protected $fillable = [
        'course_offering_id',
        'title',
        'description',
        'sort_order',
        'visible_from',
        'visible_until',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'visible_from' => 'datetime',
            'visible_until' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LmsMaterial::class)->orderBy('sort_order');
    }

    public function isVisibleNow(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        $now = now();

        if ($this->visible_from && $now->lt($this->visible_from)) {
            return false;
        }

        if ($this->visible_until && $now->gt($this->visible_until)) {
            return false;
        }

        return true;
    }
}
