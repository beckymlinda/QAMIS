<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceCategory extends Model
{
    protected $fillable = ['name', 'slug'];
}
