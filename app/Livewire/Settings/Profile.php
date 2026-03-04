<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    use ProfileValidationRules;

    public string $primer_nombre = '';
    public string $segundo_nombre = '';
    public string $primer_apellido = '';
    public string $segundo_apellido = '';
    public string $email = '';
    public string $telefono = '';
    public string $celular = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $person = $user->person;

        $this->email = $user->email ?? '';

        if ($person) {
            $this->primer_nombre = $person->primer_nombre ?? '';
            $this->segundo_nombre = $person->segundo_nombre ?? '';
            $this->primer_apellido = $person->primer_apellido ?? '';
            $this->segundo_apellido = $person->segundo_apellido ?? '';
            $this->telefono = $person->telefono ?? '';
            $this->celular = $person->celular ?? '';
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        // Actualizar email en users
        $user->email = $validated['email'];
        $user->save();

        // Actualizar datos personales en people
        if ($user->person) {
            $user->person->update([
                'primer_nombre' => $validated['primer_nombre'],
                'segundo_nombre' => $validated['segundo_nombre'] ?? '',
                'primer_apellido' => $validated['primer_apellido'],
                'segundo_apellido' => $validated['segundo_apellido'] ?? '',
                'telefono' => $validated['telefono'] ?? '',
                'celular' => $validated['celular'] ?? '',
            ]);
        }

        $fullName = trim("{$validated['primer_nombre']} {$validated['primer_apellido']}");
        $this->dispatch('profile-updated', name: $fullName);
    }
}
