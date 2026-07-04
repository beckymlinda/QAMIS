<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsOutlineItem extends Model
{
    public const TYPES = [
        'learning_outcome' => 'Learning outcome',
        'assessment_plan' => 'Assessment plan',
        'weekly_schedule' => 'Weekly schedule',
    ];

    protected $fillable = [
        'course_offering_id',
        'type',
        'title',
        'body',
        'file_path',
        'sort_order',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function hasDocument(): bool
    {
        return filled($this->file_path);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
