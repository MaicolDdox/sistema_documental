<?php

namespace App\Policies;

use App\Enums\RolGrupoEnum;
use App\Models\GroupProduct;
use App\Models\ResearchGroupUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupProductPolicy
{
    /**
     * El director de investigación puede revisar cualquier GroupProduct.
     * (La vista ya filtra por su grupo; aquí evitamos 403 inesperados.)
     */
    public function revisar(User $director, GroupProduct $producto): bool
    {
        return $director->hasRole('director_investigacion');
    }

    /**
     * Solo el autor del GroupProduct puede subir evidencias,
     * y únicamente mientras el producto no esté aprobado.
     */
    public function subirEvidencia(User $user, GroupProduct $producto): bool
    {
        return $user->id === $producto->author_id
            && $producto->estado_revision->value !== 'aprobado';
    }

    public function view(User $user, GroupProduct $producto): bool
    {
        return $user->id === $producto->author_id
            || $user->hasAnyRole(['director_investigacion', 'super_administrador']);
    }

    public function update(User $user, GroupProduct $producto): bool
    {
        return $user->id === $producto->author_id
            && $producto->estado_revision->value === 'rechazado';
    }

    public function delete(User $user, GroupProduct $producto): bool
    {
        return $user->id === $producto->author_id
            && $producto->estado_revision->value === 'pendiente';
    }
}
