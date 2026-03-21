<?php

namespace App\Http\Middleware;

use App\Support\TrainingCenterAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Los módulos por sede (semilleros, investigación, etc.) exigen usuario con centro asignado.
 */
class RequireTrainingCenter
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || TrainingCenterAccess::isSuperAdmin($user)) {
            return $next($request);
        }

        if ($user->training_center_id === null) {
            abort(403, 'Tu cuenta no tiene un centro de formación asignado. Un administrador debe vincularte a un centro para usar este módulo.');
        }

        return $next($request);
    }
}
