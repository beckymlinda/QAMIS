<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardClause extends Model
{
    protected $fillable = ['standard_area_id', 'reference_code', 'title', 'description'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(StandardArea::class, 'standard_area_id');
    }
}
