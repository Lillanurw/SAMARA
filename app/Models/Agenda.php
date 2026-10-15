<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'type', 'start_time', 'end_time', 'location', 'description', 'status', 'reminder_sent_at'
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'type' => \App\Enums\AgendaType::class,
            'status' => \App\Enums\AgendaStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
