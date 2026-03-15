<?php

namespace App\Policies;

use App\Enums\EstadoRevisionEnum;
use App\Models\GroupProduct;
use App\Models\User;

class ProductoPolicy
{
    /**
     * El investigador solo puede ver sus propios GroupProducts.
     */
    public function view(User $user, GroupProduct $groupProduct): bool
    {
        return $groupProduct->author_id === $user->id;
    }

    /**
     * Solo puede editar si es el autor y el producto está rechazado
     * (corrección tras observaciones del Director).
     */
    public function update(User $user, GroupProduct $groupProduct): bool
    {
        return $groupProduct->author_id === $user->id
            && $groupProduct->estado_revision === EstadoRevisionEnum::Rechazado;
    }

    /**
     * Solo puede eliminar si es el autor y el producto está pendiente.
     * Nunca se eliminan productos aprobados.
     */
    public function delete(User $user, GroupProduct $groupProduct): bool
    {
        return $groupProduct->author_id === $user->id
            && $groupProduct->estado_revision === EstadoRevisionEnum::Pendiente;
    }

    /**
     * Puede subir evidencias si es el autor y el producto no está aprobado.
     */
    public function subirEvidencia(User $user, GroupProduct $groupProduct): bool
    {
        return $groupProduct->author_id === $user->id
            && $groupProduct->estado_revision !== EstadoRevisionEnum::Aprobado;
    }
}
