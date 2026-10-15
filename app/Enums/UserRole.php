<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case TIM = 'TIM';
    case DIRECTOR = 'DIRECTOR';
    case DIREKTUR_UTAMA = 'DIREKTUR_UTAMA';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::TIM => 'Tim Field / Planner',
            self::DIRECTOR => 'Director',
            self::DIREKTUR_UTAMA => 'Direktur Utama',
        };
    }
}
