<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentWorkflowHistory extends Model
{
    public $timestamps = false;

    protected $table = 'assessment_workflow_history';

    protected $fillable = ['assessment_id', 'from_status', 'to_status', 'user_id', 'notes', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
