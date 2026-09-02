<x-app-layout>
    <x-slot name="header">Editar administrador del sistema</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Editar administrador del sistema</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $usuario->person?->nombre_completo ?? $usuario->email }}</p>
        </div>
        <a href="{{ route('super-admin.administradores.index') }}"
           class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all hidden sm:inline-block">
            Volver al listado
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl">
        <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/40 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            <h3 class="text-sm font-semibold text-slate-900">Rol fijo: <span class="text-amber-800">administrador_sistema</span></h3>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('super-admin.administradores.update', $usuario->id) }}" class="space-y-5" x-data="{ tieneMasRoles: {{ old('tiene_mas_roles', count($currentAdditionalRoles) > 0) ? 'true' : 'false' }} }">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nombres <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->person?->primer_nombre) }}" required
                               class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Apellidos <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->person?->primer_apellido) }}" required
                               class="w-full border @error('apellido') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('apellido')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tipo_documento" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tipo de documento <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_documento" id="tipo_documento" required
                                class="w-full border @error('tipo_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            @foreach(\App\Enums\TipoDocumentoEnum::cases() as $tipo)
                                <option value="{{ $tipo->value }}" {{ (old('tipo_documento') ?? $usuario->tipo_documento?->value) === $tipo->value ? 'selected' : '' }}>
                                    {{ ucfirst($tipo->value) }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_documento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Número de documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $usuario->numero_documento) }}" required
                               class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('numero_documento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Correo electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required
                               class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="training_center_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Centro de formación <span class="text-slate-400 font-normal">(opcional)</span>
                        </label>
                        <select name="training_center_id" id="training_center_id"
                                class="w-full border @error('training_center_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all bg-white">
                            <option value="">— Sin centro asignado —</option>
                            @foreach($centros as $centro)
                                <option value="{{ $centro->id }}" {{ (string) old('training_center_id', $usuario->training_center_id) === (string) $centro->id ? 'selected' : '' }}>
                                    {{ $centro->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('training_center_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-500 mt-1">
                            Cada centro admite un solo administrador del sistema.
                        </p>
                    </div>

                </div>

                {{-- FEAT-20260830-001: roles adicionales (multi-rol) --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3.5">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="tiene_mas_roles" id="tiene_mas_roles" value="1"
                               x-model="tieneMasRoles"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]/30 cursor-pointer">
                        <div>
                            <label for="tiene_mas_roles" class="text-sm font-medium text-slate-800 cursor-pointer select-none">
                                ¿Este usuario tiene más roles?
                            </label>
                            <p class="text-xs text-slate-500 mt-0.5">Además de administrador_sistema, podrá tener funciones activas de otros roles y cambiar entre ellos desde "Mis roles".</p>
                        </div>
                    </div>
                    <div x-show="tieneMasRoles" x-cloak class="mt-3.5 pt-3.5 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($rolesAdicionales as $r)
                            <label class="flex items-center gap-2 text-sm text-slate-700 border border-slate-200 rounded-lg px-3 py-2 bg-white hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="additional_roles[]" value="{{ $r->name }}"
                                       {{ in_array($r->name, old('additional_roles', $currentAdditionalRoles), true) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]">
                                {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="submit"
                            class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">
                        Guardar cambios
                    </button>
                    <a href="{{ route('super-admin.administradores.index') }}"
                       class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg text-sm transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
