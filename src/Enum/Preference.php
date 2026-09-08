<?php

namespace App\Enum;

enum Preference: string
{
    case PARRAIN = 'PARRAIN';
    case FILLEUL = 'FILLEUL';

    public function label(): string
    {
        return match ($this) {
            self::PARRAIN => 'Devenir parrain / marraine',
            self::FILLEUL => 'Être parrainé·e',
        };
    }
}
