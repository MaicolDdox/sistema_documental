<?php

namespace App\Enums;

enum EstadoRevisionEnum: string
{
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Aprobado = 'aprobado';
    case Rechazado = 'rechazado';
}
