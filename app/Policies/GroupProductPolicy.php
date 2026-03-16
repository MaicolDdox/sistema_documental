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
}
