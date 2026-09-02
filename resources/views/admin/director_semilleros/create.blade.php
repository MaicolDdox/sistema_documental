<x-app-layout>
    <x-slot name="header">Crear Director de Semilleros</x-slot>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Crear Director de Semilleros</h2>
        <p class="text-sm text-slate-500 mt-1">Este formulario crea usuarios únicamente con el rol <strong>Director de Semilleros</strong>.</p>
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
        <form method="POST" action="{{ route('admin.director-semilleros.store') }}" class="space-y-5" x-data="{ tieneMasRoles: {{ old('tiene_mas_roles') ? 'true' : 'false' }} }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                           class="w-full border @error('apellido') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('apellido')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="tipo_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Documento <span class="text-red-500">*</span></label>
                    <select name="tipo_documento" id="tipo_documento" required
                            class="w-full border @error('tipo_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        <option value="">Selecciona un tipo...</option>
                        @foreach(\App\Enums\TipoDocumentoEnum::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_documento') === $tipo->value ? 'selected' : '' }}>
                                {{ ucfirst($tipo->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_documento')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Número de Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" required
                           class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('numero_documento')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña Temporal <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required
                           class="w-full border @error('password') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <p class="text-xs text-slate-500 mt-1">Mínimo 8 caracteres.</p>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3 text-sm text-slate-600">
                    Rol asignado: <strong class="text-slate-800">Director de Semilleros</strong> — centro de formación: <strong class="text-slate-800">{{ auth()->user()->trainingCenter?->nombre ?? 'tu centro' }}</strong> (heredado automáticamente).
                </div>
            </div>

            {{-- FEAT-20260830-001: roles adicionales (multi-rol), solo en creación --}}
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3.5">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="tiene_mas_roles" id="tiene_mas_roles" value="1"
                           x-model="tieneMasRoles"
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]/30 cursor-pointer">
                    <div>
                        <label for="tiene_mas_roles" class="text-sm font-medium text-slate-800 cursor-pointer select-none">
                            ¿Este usuario tiene más roles?
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">Además de Director de Semilleros, podrá tener funciones activas de otros roles y cambiar entre ellos desde "Mis roles".</p>
                    </div>
                </div>
                <div x-show="tieneMasRoles" x-cloak class="mt-3.5 pt-3.5 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($rolesAdicionales as $r)
                        <label class="flex items-center gap-2 text-sm text-slate-700 border border-slate-200 rounded-lg px-3 py-2 bg-white hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="additional_roles[]" value="{{ $r->name }}"
                                   {{ in_array($r->name, old('additional_roles', []), true) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]">
                            {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3.5 flex items-start gap-3">
                <input type="checkbox" name="enviar_credenciales" id="enviar_credenciales" value="1"
                       {{ old('enviar_credenciales') ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]/30 cursor-pointer">
                <div>
                    <label for="enviar_credenciales" class="text-sm font-medium text-slate-800 cursor-pointer select-none">
                        Enviar credenciales por correo
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">Se enviará un correo al usuario con su email y contraseña temporal.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.usuarios.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
