<?php

namespace App\Enums;

enum TipoProyectoOrigenEnum: string
{
    case SGPS = 'SGPS';
    case CapacidadInstalada = 'CAPACIDAD INSTALADA';
    case Formativa = 'FORMATIVA';
    case IniciativaCentro = 'INICIATIVA CENTRO';
    case Articulacion = 'ARTICULACION';
    case Semilleros = 'SEMILLEROS';
    case Otro = 'OTRO';
}
