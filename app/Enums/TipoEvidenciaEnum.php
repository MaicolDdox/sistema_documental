<?php

namespace App\Enums;

enum TipoEvidenciaEnum: string
{
    case Desarrollo = 'desarrollo';
    case Formulacion = 'formulacion';
    case Ejecucion = 'ejecucion';
    case ProductoFinal = 'producto_final';
}
