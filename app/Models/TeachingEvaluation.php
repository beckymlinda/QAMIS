<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeachingEvaluation extends Model
{
    protected $fillable = [
        'student_id', 'course_offering_id', 'evaluation_period_id',
        'submitted_at', 'status',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TeachingEvaluationResponse::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
