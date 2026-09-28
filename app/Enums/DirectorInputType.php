<?php

namespace App\Enums;

enum DirectorInputType: string
{
    case STRATEGIC_DIRECTION = 'STRATEGIC_DIRECTION';
    case SUGGESTION = 'SUGGESTION';
    case SOLUTION = 'SOLUTION';
    case DECISION = 'DECISION';

    public function label(): string
    {
        return match($this) {
            self::STRATEGIC_DIRECTION => 'Strategic Direction',
            self::SUGGESTION => 'Suggestion',
            self::SOLUTION => 'Solution',
            self::DECISION => 'Decision',
        };
    }
}
