<?php

namespace App\Notifications;

use App\Models\FollowUp;
use App\Models\VisitReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportFollowUpRequiredNotification extends Notification
{
    use Queueable;

    public VisitReport $report;
    public FollowUp $followUp;

    /**
     * Create a new notification instance.
     */
    public function __construct(VisitReport $report, FollowUp $followUp)
    {
        $this->report = $report;
        $this->followUp = $followUp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $customerName = $this->report->visitPlan?->customer?->name ?? 'Customer';
        $submitterName = $this->report->submitter?->full_name ?? 'Tim Sales';
        $priorityLabel = is_object($this->followUp->priority) && method_exists($this->followUp->priority, 'label')
            ? $this->followUp->priority->label()
            : (is_object($this->followUp->priority) ? $this->followUp->priority->value : (string) $this->followUp->priority);

        return [
            'type' => 'report_follow_up_required',
            'title' => 'Laporan Butuh Tindak Lanjut',
            'message' => "Laporan {$this->report->report_number} dari {$submitterName} ({$customerName}) memerlukan tindak lanjut: \"{$this->followUp->action_title}\"",
            'report_id' => $this->report->id,
            'report_number' => $this->report->report_number,
            'follow_up_id' => $this->followUp->id,
            'customer_name' => $customerName,
            'submitter_name' => $submitterName,
            'action_title' => $this->followUp->action_title,
            'due_date' => $this->followUp->due_date ? $this->followUp->due_date->format('d/m/Y') : null,
            'priority' => $priorityLabel,
            'url' => route('reports.show', $this->report->id),
        ];
    }
}
