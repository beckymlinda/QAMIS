<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssignmentSubmission extends Model
{
    protected $fillable = [
        'lms_assignment_id',
        'student_id',
        'body',
        'file_path',
        'marked_file_path',
        'annotation_data',
        'score',
        'feedback',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'annotation_data' => 'array',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isGraded(): bool
    {
        return $this->graded_at !== null;
    }

    public function hasSubmissionFile(): bool
    {
        return filled($this->file_path);
    }

    public function hasMarkedFile(): bool
    {
        return filled($this->marked_file_path);
    }

    public function isPdfSubmission(): bool
    {
        return $this->hasSubmissionFile() && str_ends_with(strtolower($this->file_path), '.pdf');
    }
}
