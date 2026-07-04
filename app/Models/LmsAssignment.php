<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignment extends Model
{
    protected $fillable = [
        'course_offering_id',
        'title',
        'instructions',
        'attachment_file_path',
        'due_at',
        'max_score',
        'allow_late',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allow_late' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LmsAssignmentSubmission::class);
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_file_path);
    }

    public function isOpenForSubmission(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if ($this->due_at === null) {
            return true;
        }

        if ($this->allow_late) {
            return true;
        }

        return now()->lte($this->due_at);
    }
}
