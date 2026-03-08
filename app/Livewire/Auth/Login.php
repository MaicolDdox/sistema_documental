<?php

namespace App\Livewire\Auth;

use App\Enums\EstadoEnum;
use App\Models\User;
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
            if ($user->hasRole('lider_semillero')) {
                $this->redirect('/lider-semillero');
                return;
            }
            if ($user->hasRole('director_semilleros')) {
                $this->redirect('/director-semilleros');
                return;
            }
            if ($user->hasRole('administrador_sistema') || $user->hasRole('admin')) {
                $this->redirect('/admin/dashboard');
                return;
            }
            $this->redirectRoute('dashboard');
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

        Auth::login($user, $this->remember);

        request()->session()->regenerate();

        // Redirigir según rol al módulo correspondiente (igual que LoginResponse)
        if ($user->hasRole('administrador_sistema') || $user->hasRole('admin')) {
            return redirect()->to('/admin/dashboard');
        }
        if ($user->hasRole('director_semilleros')) {
            return redirect()->to('/director-semilleros');
        }
        if ($user->hasRole('lider_semillero')) {
            return redirect()->to('/lider-semillero');
        }
        if ($user->hasRole('director_investigacion') || $user->hasRole('investigador_asociado')) {
            return redirect()->to('/research/dashboard');
        }
        if ($user->hasRole('asesor')) {
            return redirect()->to('/seedlings');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
