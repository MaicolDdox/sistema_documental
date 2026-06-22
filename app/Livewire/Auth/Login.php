<?php

namespace App\Livewire\Auth;

use App\Enums\EstadoEnum;
use App\Models\User;
use App\Support\RoleModuleLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $tipo_documento = '';
    public string $numero_documento = '';
    public string $password = '';
    public bool $remember = false;

    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->load('roles');
            $this->redirect(RoleModuleLinks::dashboardUrlForUser($user));

            return;
        }
    }

    public function login()
    {
        $this->validate([
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('tipo_documento', $this->tipo_documento)
                    ->where('numero_documento', $this->numero_documento)
                    ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'tipo_documento' => trans('auth.failed'),
            ]);
        }

        if ($user->estado !== EstadoEnum::Activo) {
            throw ValidationException::withMessages([
                'numero_documento' => 'Tu cuenta no está activa. Contacta al administrador.',
            ]);
        }

        if (! Hash::check($this->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }

        // ─────────────────────────────────────────────────
        // Redirigir al reto de 2FA si el usuario lo tiene confirmado
        // ─────────────────────────────────────────────────
        if ($user->two_factor_secret && ! is_null($user->two_factor_confirmed_at)) {
            session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $this->remember,
            ]);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, $this->remember);

        session()->regenerate();

        $user->load('roles');

        return redirect()->intended(RoleModuleLinks::dashboardUrlForUser($user));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
