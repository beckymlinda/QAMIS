<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseResult extends Model
{
    protected $fillable = [
        'student_course_enrolment_id',
        'coursework_percentage', 'exam_percentage', 'final_percentage',
        'letter_grade', 'grade_points', 'quality_label', 'academic_decision',
        'is_published', 'graded_by_staff_member_id', 'graded_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'coursework_percentage' => 'decimal:2',
            'exam_percentage' => 'decimal:2',
            'final_percentage' => 'decimal:2',
            'grade_points' => 'decimal:2',
            'is_published' => 'boolean',
            'graded_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(StudentCourseEnrolment::class, 'student_course_enrolment_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'graded_by_staff_member_id');
    }
}
