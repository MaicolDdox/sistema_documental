<?php

namespace App\Enums;

enum RolGrupoEnum: string
{
    case Director          = 'director';
    case InvestigadorLider = 'investigador_lider';
    case InvestigadorAsociado = 'investigador_asociado';
    case Integrante        = 'integrante';
}
