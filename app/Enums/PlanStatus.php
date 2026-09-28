<?php

namespace App\Enums;

enum PlanStatus: string
{
    case DRAFT = 'DRAFT';
    case PLANNED = 'PLANNED';
    case APPROVED = 'APPROVED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case RESCHEDULED = 'RESCHEDULED';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PLANNED => 'Terencana',
            self::APPROVED => 'Disetujui',
            self::IN_PROGRESS => 'Sedang Kunjungan',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::RESCHEDULED => 'Dijadwal Ulang',
        };
    }
}
