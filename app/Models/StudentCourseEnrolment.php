<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourseEnrolment extends Model
{
    protected $fillable = ['student_id', 'course_offering_id', 'status'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function result(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CourseResult::class, 'student_course_enrolment_id');
    }
}
