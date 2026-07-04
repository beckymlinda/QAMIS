<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseOffering extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'course_id', 'staff_member_id',
        'academic_year', 'semester', 'delivery_mode',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'staff_member_id');
    }

    public function studentEnrolments(): HasMany
    {
        return $this->hasMany(StudentCourseEnrolment::class);
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function teachingEvaluations(): HasMany
    {
        return $this->hasMany(TeachingEvaluation::class);
    }

    public function lmsOutline(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LmsCourseOutline::class);
    }

    public function lmsModules(): HasMany
    {
        return $this->hasMany(LmsModule::class)->orderBy('sort_order');
    }

    public function lmsAnnouncements(): HasMany
    {
        return $this->hasMany(LmsAnnouncement::class)->latest('published_at');
    }

    public function lmsAssignments(): HasMany
    {
        return $this->hasMany(LmsAssignment::class)->orderBy('due_at');
    }

    public function lmsDiscussions(): HasMany
    {
        return $this->hasMany(LmsDiscussion::class)->latest();
    }

    public function lmsOutlineItems(): HasMany
    {
        return $this->hasMany(LmsOutlineItem::class)->orderBy('type')->orderBy('sort_order');
    }

    public function label(): string
    {
        return $this->course?->displayLabel() ?? 'Course offering';
    }
}
