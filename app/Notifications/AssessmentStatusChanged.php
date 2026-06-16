<?php

namespace App\Notifications;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Assessment $assessment,
        public AssessmentStatus $from,
        public AssessmentStatus $to
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assessment status updated: '.$this->assessment->title)
            ->line("Assessment \"{$this->assessment->title}\" changed from {$this->from->label()} to {$this->to->label()}.")
            ->action('View Assessment', url('/assessments/'.$this->assessment->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assessment_id' => $this->assessment->id,
            'title' => $this->assessment->title,
            'from' => $this->from->value,
            'to' => $this->to->value,
            'message' => "Assessment status changed to {$this->to->label()}",
        ];
    }
}
