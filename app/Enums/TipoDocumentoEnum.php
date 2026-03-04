<?php

namespace App\Enums;

enum TipoDocumentoEnum: string
{
    case DocumentoIdentidad = 'documento identidad';
    case CedulaCiudadana = 'cedula ciudadana';
    case Pasaporte = 'pasaporte';
    case CedulaExtrangera = 'cedula extrangera';
}
