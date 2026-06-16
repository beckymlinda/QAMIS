<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceDocumentVersion extends Model
{
    protected $fillable = [
        'evidence_document_id', 'version_no', 'file_path', 'original_filename',
        'mime_type', 'checksum', 'uploaded_by', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EvidenceDocument::class, 'evidence_document_id');
    }
}
