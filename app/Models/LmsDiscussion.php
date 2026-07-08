<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsDiscussion extends Model
{
    protected $fillable = [
        'course_offering_id',
        'created_by',
        'title',
        'body',
        'is_pinned',
        'is_closed',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return ! $this->is_closed;
    }

    public function isCreator(?User $user): bool
    {
        return $user !== null && $this->created_by === $user->id;
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(LmsDiscussionPost::class)->orderBy('created_at');
    }
}
