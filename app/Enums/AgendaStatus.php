<?php

namespace App\Enums;

enum AgendaStatus: string
{
    case TERJADWAL = 'TERJADWAL';
    case SELESAI = 'SELESAI';
    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return match($this) {
            self::TERJADWAL => 'Terjadwal',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }
}
