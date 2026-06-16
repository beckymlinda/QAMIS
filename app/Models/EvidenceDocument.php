<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvidenceDocument extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = ['institution_id', 'evidence_category_id', 'title', 'description', 'current_version_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EvidenceCategory::class, 'evidence_category_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EvidenceDocumentVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(EvidenceDocumentVersion::class, 'current_version_id');
    }
}
