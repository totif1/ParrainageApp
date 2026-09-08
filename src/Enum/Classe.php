<?php

namespace App\Enum;

enum Classe: string
{
    case BUT1INFO = 'BUT1INFO';
    case BUT2INFO = 'BUT2INFO';
    case BUT3INFO = 'BUT3INFO';
    case BUT1GEA = 'BUT1GEA';
    case BUT2GEA = 'BUT2GEA';
    case BUT3GEA = 'BUT3GEA';
    case BTS1AC = 'BTS1AC';
    case BTS2AC = 'BTS2AC';

    public function label(): string
    {
        return match ($this) {
            self::BUT1INFO => 'BUT 1 Informatique',
            self::BUT2INFO => 'BUT 2 Informatique',
            self::BUT3INFO => 'BUT 3 Informatique',
            self::BUT1GEA => 'BUT 1 GEA',
            self::BUT2GEA => 'BUT 2 GEA',
            self::BUT3GEA => 'BUT 3 GEA',
            self::BTS1AC => 'BTS 1 AC',
            self::BTS2AC => 'BTS 2 AC',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::BUT1INFO => 'BUT 1 Info',
            self::BUT2INFO => 'BUT 2 Info',
            self::BUT3INFO => 'BUT 3 Info',
            self::BUT1GEA => 'BUT 1 GEA',
            self::BUT2GEA => 'BUT 2 GEA',
            self::BUT3GEA => 'BUT 3 GEA',
            self::BTS1AC => 'BTS 1 AC',
            self::BTS2AC => 'BTS 2 AC',
        };
    }
}
