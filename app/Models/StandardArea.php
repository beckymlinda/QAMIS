<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardArea extends Model
{
    protected $fillable = ['standard_version_id', 'parent_id', 'code', 'title', 'sort_order'];

    public function version(): BelongsTo
    {
        return $this->belongsTo(StandardVersion::class, 'standard_version_id');
    }

    public function clauses(): HasMany
    {
        return $this->hasMany(StandardClause::class);
    }
}
