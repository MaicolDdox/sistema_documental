<div>
    <flux:heading size="xl">{{ __('Editar Usuario') }}</flux:heading>
    <p class="text-slate-500 text-sm mt-1">{{ $user->person?->primer_nombre }} {{ $user->person?->primer_apellido }}</p>

    @if (session('status'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-600 text-sm font-medium">{{ session('status') }}</p>
        </div>
    @endif

    <form wire:submit="update" class="mt-8 max-w-3xl space-y-8">

        {{-- Datos de autenticación --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold text-slate-800 mb-2">Datos de Acceso</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento *</label>
                    <select wire:model="tipo_documento" required class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo->value }}">{{ ucfirst(str_replace('_', ' ', $tipo->value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <flux:input wire:model="numero_documento" label="Número de documento *" type="number" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="email" label="Email" type="email" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado *</label>
                    <select wire:model="estado" required class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($estados as $e)
                            <option value="{{ $e->value }}">{{ ucfirst($e->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Centro de formación @if($centerSelectReadonly)<span class="text-slate-400 font-normal">(asignado)</span>@endif</label>
                @if($centerSelectReadonly)
                    <p class="text-sm text-slate-700 py-2 px-3 rounded-lg border border-slate-200 bg-slate-50">{{ $trainingCenters->first()?->nombre ?? '—' }}</p>
                    <input type="hidden" wire:model="training_center_id" />
                @else
                    <select wire:model="training_center_id" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Sin centro</option>
                        @foreach($trainingCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->nombre }}</option>
                        @endforeach
                    </select>
                    @if($role === 'administrador_sistema')
                        <p class="text-xs text-amber-700 mt-1">Puedes dejar <strong>Sin centro</strong> para vincularlo luego en Super admin → Centro ↔ administrador.</p>
                    @endif
                @endif
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

        {{-- Rol principal: no se eliminan otros roles; solo se asegura el elegido (ver UserEdit::update). --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold text-slate-800 mb-2">Rol del sistema</legend>
            <select wire:model="role" required class="w-full rounded-lg border-slate-300 text-sm">
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500">Los demás roles asignados se mantienen. Este es tu <span class="font-medium">rol principal</span> (menú e inicio de sesión). Solo se agrega el rol si aún no lo tenía.</p>
        </fieldset>

        <div class="flex items-center gap-4 pt-4">
            <flux:button variant="primary" type="submit">Guardar Cambios</flux:button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Volver</a>
        </div>
    </form>
</div>
