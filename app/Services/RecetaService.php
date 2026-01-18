<?php

namespace App\Services;

use App\Models\Receta;
use DomainException;

class RecetaService
{
    /**
     * Comprueba si una receta puede modificarse según reglas de negocio.
     */
    public function assertCanBeModified(Receta $receta): void
    {
        if ($receta->publicada) {
            throw new DomainException(
                'No se puede modificar una receta ya publicada'
            );
        }
    }
}
