<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsCourseOutline extends Model
{
    protected $fillable = [
        'course_offering_id',
        'learning_outcomes',
        'assessment_plan',
        'weekly_schedule',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }
}
