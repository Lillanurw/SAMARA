<?php

namespace App\Notifications;

use App\Models\DirectorInput;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DirectorInputCreatedNotification extends Notification
{
    use Queueable;

    public DirectorInput $directorInput;

    /**
     * Create a new notification instance.
     */
    public function __construct(DirectorInput $directorInput)
    {
        $this->directorInput = $directorInput;
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
        $creatorName = $this->directorInput->creator?->full_name ?? 'Director';
        $inputTypeLabel = is_object($this->directorInput->input_type) && method_exists($this->directorInput->input_type, 'label')
            ? $this->directorInput->input_type->label()
            : (string) $this->directorInput->input_type;

        return [
            'type' => 'director_input_created',
            'title' => 'Arahan/Saran Director Baru',
            'message' => "{$creatorName} memberikan arahan/saran: \"{$this->directorInput->topic}\"",
            'director_input_id' => $this->directorInput->id,
            'topic' => $this->directorInput->topic,
            'input_type' => $inputTypeLabel,
            'direction_text' => $this->directorInput->direction_text,
            'creator_name' => $creatorName,
            'priority' => $this->directorInput->priority instanceof \BackedEnum ? $this->directorInput->priority->value : (string) $this->directorInput->priority,
            'due_date' => $this->directorInput->due_date ? $this->directorInput->due_date->format('d/m/Y') : null,
            'url' => route('directions.myDirections'),
        ];
    }
}
