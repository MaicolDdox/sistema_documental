<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DeleteUserController extends Controller
{
    use PasswordValidationRules;

    /**
     * Elimina la cuenta del usuario autenticado.
     * Requiere confirmación de contraseña antes de proceder.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        $user->person?->delete();
        $user->delete();

        return redirect('/');
    }
}
