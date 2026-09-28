<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case TIM = 'TIM';
    case DIRECTOR = 'DIRECTOR';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::TIM => 'Tim Field / Planner',
            self::DIRECTOR => 'Director',
        };
    }
}
