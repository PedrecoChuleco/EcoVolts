<?php

namespace App\Enums;

enum RoofDirection: string
{
    case Norte = 'Norte';
    case Leste = 'Leste';
    case Oeste = 'Oeste';
    case Sul = 'Sul';

    public function efficiency(): float
    {
        return match ($this) {
            self::Norte => 1.0,
            self::Leste, self::Oeste => 0.85,
            self::Sul => 0.70,
        };
    }
}
