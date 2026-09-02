<?php

namespace App\Enums;

enum NivelFormacionEnum: string
{
    case Tecnico = 'tecnico';
    case Tecnologo = 'tecnologo';
    case Pregrado = 'pregrado';
    case Posgrado = 'posgrado';

    public function label(): string
    {
        return match ($this) {
            self::Tecnico => 'Técnico',
            self::Tecnologo => 'Tecnólogo',
            self::Pregrado => 'Pregrado',
            self::Posgrado => 'Posgrado',
        };
    }
}
