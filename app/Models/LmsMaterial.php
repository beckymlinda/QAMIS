<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsMaterial extends Model
{
    public const TYPES = [
        'document' => 'Document',
        'pdf' => 'PDF',
        'presentation' => 'Presentation',
        'video_link' => 'Video link',
        'audio_link' => 'Audio link',
        'link' => 'Web link',
        'other' => 'Other',
    ];

    protected $fillable = [
        'lms_module_id',
        'title',
        'type',
        'file_path',
        'external_url',
        'sort_order',
        'allow_download',
    ];

    protected function casts(): array
    {
        return [
            'allow_download' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LmsModule::class, 'lms_module_id');
    }

    public function isLink(): bool
    {
        return in_array($this->type, ['video_link', 'audio_link', 'link'], true);
    }
}
