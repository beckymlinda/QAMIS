<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringRubric extends Model
{
    protected $fillable = ['standard_version_id', 'score', 'label', 'description'];
}
