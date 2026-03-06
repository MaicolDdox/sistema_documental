<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\EntityPosition;
use App\Models\LinkageType;
use App\Models\TrainingProgram;
use App\Enums\GeneroEnum;
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
    
    // Nuevos campos
    public ?string $genero = null;
    public string $eps = '';
    public string $email_institucional = '';
    public ?int $entity_position_id = null;
    public ?int $linkage_type_id = null;
    public ?int $training_program_id = null;

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
            $this->genero = $person->genero?->value ?? null;
            $this->eps = $person->eps ?? '';
            $this->email_institucional = $person->email_institucional ?? '';
            $this->entity_position_id = $person->entity_position_id;
            $this->linkage_type_id = $person->linkage_type_id;
            $this->training_program_id = $person->training_program_id;
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
                'genero' => $validated['genero'],
                'eps' => $validated['eps'] ?? '',
                'email_institucional' => $validated['email_institucional'] ?? '',
                'entity_position_id' => $validated['entity_position_id'],
                'linkage_type_id' => $validated['linkage_type_id'],
                'training_program_id' => $validated['training_program_id'],
            ]);
        }

        $fullName = trim("{$validated['primer_nombre']} {$validated['primer_apellido']}");
        $this->dispatch('profile-updated', name: $fullName);
    }

    public function render()
    {
        return view('livewire.settings.profile', [
            'entityPositions' => EntityPosition::orderBy('nombre')->get(),
            'linkageTypes' => LinkageType::orderBy('nombre')->get(),
            'trainingPrograms' => TrainingProgram::orderBy('nombre')->get(),
            'generos' => GeneroEnum::cases(),
        ]);
    }
}
