<?php

namespace App\Enum;

enum FilmStatus: string
{
    case DISPONIBLE = 'disponible';
    case ARCHIVE = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIBLE => "A l'affiche",
            self::ARCHIVE => 'Hors programmation',
        };
    }
}
