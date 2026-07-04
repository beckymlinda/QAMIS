<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSlot extends Model
{
    protected $fillable = [
        'course_offering_id', 'classroom_id', 'day_of_week',
        'start_time', 'end_time', 'session_type', 'venue_name',
    ];

    protected function casts(): array
    {
        return [];
    }

    public static function dayNames(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }

    public function dayName(): string
    {
        return self::dayNames()[$this->day_of_week] ?? 'Unknown';
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function venueLabel(): string
    {
        return $this->classroom?->name ?? $this->venue_name ?? 'TBA';
    }
}
