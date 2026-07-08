<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsDiscussionPost extends Model
{
    protected $fillable = [
        'lms_discussion_id',
        'user_id',
        'parent_id',
        'body',
        'file_path',
        'file_name',
    ];

    public function hasAttachment(): bool
    {
        return filled($this->file_path);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(LmsDiscussion::class, 'lms_discussion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }
}
