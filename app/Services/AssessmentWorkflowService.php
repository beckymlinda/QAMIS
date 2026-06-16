<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\AssessmentWorkflowHistory;
use App\Notifications\AssessmentStatusChanged;
use Illuminate\Support\Facades\Notification;

class AssessmentWorkflowService
{
    public function __construct(protected ComplianceEngine $complianceEngine) {}

    public function transition(Assessment $assessment, AssessmentStatus $toStatus, ?string $notes = null): Assessment
    {
        $from = $assessment->status;

        if (! $from->canTransitionTo($toStatus)) {
            throw new \InvalidArgumentException("Cannot transition from {$from->value} to {$toStatus->value}");
        }

        $assessment->status = $toStatus;

        match ($toStatus) {
            AssessmentStatus::Submitted => $assessment->submitted_at = now(),
            AssessmentStatus::Reviewed => $assessment->reviewed_at = now(),
            AssessmentStatus::Approved => $assessment->approved_at = now(),
            AssessmentStatus::Locked => $assessment->locked_at = now(),
            default => null,
        };

        if ($toStatus === AssessmentStatus::Approved) {
            $assessment->approved_by = auth()->id();
        }

        $assessment->save();

        AssessmentWorkflowHistory::create([
            'assessment_id' => $assessment->id,
            'from_status' => $from->value,
            'to_status' => $toStatus->value,
            'user_id' => auth()->id(),
            'notes' => $notes,
            'created_at' => now(),
        ]);

        if (in_array($toStatus, [AssessmentStatus::Submitted, AssessmentStatus::Reviewed, AssessmentStatus::Approved], true)) {
            $this->complianceEngine->compute($assessment);
        }

        $institution = $assessment->institution;
        if ($institution) {
            Notification::send(
                $institution->users()->role(['qa_officer', 'institution_admin'])->get(),
                new AssessmentStatusChanged($assessment, $from, $toStatus)
            );
        }

        return $assessment->fresh();
    }
}
