<x-app-layout>
    <x-slot name="header">Editar Usuario</x-slot>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Editar Usuario</h2>
        <p class="text-sm text-slate-500 mt-1">Actualiza los datos del usuario en el sistema.</p>
    </div>
    <a href="{{ route('admin.usuarios.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all hidden sm:inline-block">
        Volver al listado
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-4xl">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <h3 class="text-sm font-semibold text-slate-900">Información del Usuario</h3>
    </div>
    
    <div class="p-5">
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}" class="space-y-5" x-data="{ tieneMasRoles: {{ old('tiene_mas_roles', count($currentAdditionalRoles) > 0) ? 'true' : 'false' }} }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->person?->primer_nombre) }}" required
                           class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->person?->primer_apellido) }}" required
                           class="w-full border @error('apellido') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('apellido')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Numero de Documento -->
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Número de Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $usuario->numero_documento) }}" required
                           class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('numero_documento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required
                           class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rol -->
                @php
                    $namesRol = $usuario->roles->pluck('name')->all();
                    $rolPrincipalVista = ($usuario->primary_role_name && in_array($usuario->primary_role_name, $namesRol, true))
                        ? $usuario->primary_role_name
                        : (\App\Support\RoleModuleLinks::pickPrimaryRoleNameFromNames($namesRol) ?? $usuario->roles->first()?->name);
                @endphp
                <div>
                    <label for="rol" class="block text-sm font-medium text-slate-700 mb-1.5">Rol principal <span class="text-red-500">*</span></label>
                    <select name="rol" id="rol" required
                            class="w-full border @error('rol') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        <option value="">Selecciona un rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ (old('rol') ?? $rolPrincipalVista) == $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-500 mt-1">No se quitan otros roles. El rol que elijas aquí queda como <span class="font-medium">principal</span> (inicio de sesión y menú). Al agregar roles desde otras pantallas, el principal ya no cambia solo por prioridad automática.</p>
                </div>

                @if($trainingCenters->isNotEmpty())
                <div class="md:col-span-2">
                    <label for="training_center_id" class="block text-sm font-medium text-slate-700 mb-1.5">Centro de formación</label>
                    <select name="training_center_id" id="training_center_id"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        <option value="">Sin centro (para vincular en Centro ↔ administrador)</option>
                        @foreach($trainingCenters as $tc)
                            <option value="{{ $tc->id }}" {{ (string) old('training_center_id', $usuario->training_center_id) === (string) $tc->id ? 'selected' : '' }}>
                                {{ $tc->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('training_center_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-500 mt-1">Solo super administrador. Si el rol es <strong>administrador del sistema</strong>, cada centro admite <strong>un solo</strong> usuario con ese vínculo; lo habitual es asignarlo en <strong>Centro ↔ administrador</strong>. Deja sin centro para que vuelva a aparecer allí.</p>
                </div>
                @endif
            </div>

            {{-- FEAT-20260830-001: roles adicionales (multi-rol), en edición --}}
            @if($canManageAdditionalRoles)
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3.5">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="tiene_mas_roles" id="tiene_mas_roles" value="1"
                           x-model="tieneMasRoles"
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]/30 cursor-pointer">
                    <div>
                        <label for="tiene_mas_roles" class="text-sm font-medium text-slate-800 cursor-pointer select-none">
                            ¿Este usuario tiene más roles?
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">Además del rol principal, podrá tener funciones activas de otros roles y cambiar entre ellos desde "Mis roles".</p>
                    </div>
                </div>
                <div x-show="tieneMasRoles" x-cloak class="mt-3.5 pt-3.5 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($additionalRoleOptions as $r)
                        <label class="flex items-center gap-2 text-sm text-slate-700 border border-slate-200 rounded-lg px-3 py-2 bg-white hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="additional_roles[]" value="{{ $r->name }}"
                                   {{ in_array($r->name, old('additional_roles', $currentAdditionalRoles), true) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]">
                            {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                        </label>
                    @endforeach
                </div>
            </div>
            @elseif(count($currentAdditionalRoles) > 0)
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3.5">
                <p class="text-sm font-medium text-slate-800">Roles adicionales</p>
                <p class="text-xs text-slate-500 mt-0.5">Este usuario ya tiene roles adicionales asignados, pero no tienes permiso para modificarlos aquí (no puedes editar los tuyos propios).</p>
            </div>
            @endif

            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.usuarios.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
