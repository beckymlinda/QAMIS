<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'user_id', 'programme_id', 'student_number',
        'first_name', 'last_name', 'email', 'phone', 'year_of_study', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function courseEnrolments(): HasMany
    {
        return $this->hasMany(StudentCourseEnrolment::class);
    }

    public function teachingEvaluations(): HasMany
    {
        return $this->hasMany(TeachingEvaluation::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(StudentFeePayment::class);
    }

    public function programmeApplication(): HasOne
    {
        return $this->hasOne(ProgrammeApplication::class, 'enrolled_student_id');
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
