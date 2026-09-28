<?php

namespace App\Enums;

enum OutcomeType: string
{
    case NO_CHANGE = 'NO_CHANGE';
    case NEED_IDENTIFIED = 'NEED_IDENTIFIED';
    case FOLLOW_UP = 'FOLLOW_UP';
    case PROPOSAL = 'PROPOSAL';
    case NEGOTIATION = 'NEGOTIATION';
    case WON = 'WON';
    case LOST = 'LOST';

    public function label(): string
    {
        return match($this) {
            self::NO_CHANGE => 'Tidak Ada Perubahan',
            self::NEED_IDENTIFIED => 'Kebutuhan Teridentifikasi',
            self::FOLLOW_UP => 'Tindak Lanjut Diperlukan',
            self::PROPOSAL => 'Tahap Proposal',
            self::NEGOTIATION => 'Tahap Negosiasi',
            self::WON => 'Deal / Menang',
            self::LOST => 'Batal / Kalah',
        };
    }
}
