<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    use PasswordValidationRules;

    /**
     * Actualiza la contraseña del usuario autenticado.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => $this->currentPasswordRules(),
                'password'         => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            return redirect()->route('settings.password')
                ->withErrors($e->errors())
                ->withInput();
        }

        Auth::user()->update(['password' => $validated['password']]);

        return redirect()->route('settings.password')
            ->with('success', '¡Contraseña actualizada correctamente!');
    }
}
