<?php

namespace App\Enums;

enum EstadoCaso: string
{
    case EnTramite = 'en_tramite';
    case Archivado = 'archivado';
    case Suspendido = 'suspendido';
    case Finalizado = 'finalizado';
}
