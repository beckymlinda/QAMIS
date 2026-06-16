<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveAction extends Model
{
    use Auditable;

    protected $fillable = [
        'recommendation_id', 'assigned_to', 'deadline', 'status',
        'progress_notes', 'completion_evidence_id', 'escalated_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'escalated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
