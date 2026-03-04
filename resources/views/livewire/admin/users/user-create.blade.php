<div>
    <flux:heading size="xl">{{ __('Crear Nuevo Usuario') }}</flux:heading>
    <p class="text-slate-500 text-sm mt-1">El usuario se creará con estado <strong>inactivo</strong> por defecto.</p>

    <form wire:submit="store" class="mt-8 max-w-3xl space-y-8">

        {{-- Datos de autenticación --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold text-slate-800 mb-2">Datos de Acceso</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento *</label>
                    <select wire:model="tipo_documento" required class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Seleccionar...</option>
                        @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo->value }}">{{ ucfirst(str_replace('_', ' ', $tipo->value)) }}</option>
                        @endforeach
                    </select>
                    @error('tipo_documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <flux:input wire:model="numero_documento" label="Número de documento *" type="number" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="email" label="Email" type="email" placeholder="opcional" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Centro de formación</label>
                    <select wire:model="training_center_id" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Seleccionar...</option>
                        @foreach($trainingCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="password" label="Contraseña *" type="password" required />
                <flux:input wire:model="password_confirmation" label="Confirmar contraseña *" type="password" required />
            </div>
        </fieldset>

        {{-- Datos personales --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold text-slate-800 mb-2">Datos Personales</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="primer_nombre" label="Primer nombre *" type="text" required />
                <flux:input wire:model="segundo_nombre" label="Segundo nombre" type="text" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="primer_apellido" label="Primer apellido *" type="text" required />
                <flux:input wire:model="segundo_apellido" label="Segundo apellido" type="text" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="telefono" label="Teléfono" type="text" />
                <flux:input wire:model="celular" label="Celular" type="text" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="eps" label="EPS" type="text" />
                <flux:input wire:model="email_institucional" label="Email institucional" type="email" />
            </div>
        </fieldset>

        {{-- Rol --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold text-slate-800 mb-2">Rol del Sistema</legend>
            <div>
                <select wire:model="role" required class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Seleccionar rol *</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
                @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </fieldset>

        <div class="flex items-center gap-4 pt-4">
            <flux:button variant="primary" type="submit">Crear Usuario</flux:button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancelar</a>
        </div>
    </form>
</div>
