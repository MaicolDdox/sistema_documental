<x-app-layout>
    <x-slot name="header">Nuevo usuario del sistema</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Nuevo usuario del sistema</h2>
            <p class="text-sm text-slate-500 mt-1">Crea un usuario con cualquier rol del sistema. Para crear administradores usa la sección <strong>Usuarios Administradores</strong>.</p>
        </div>
        <a href="{{ route('super-admin.usuarios-sistema.index') }}"
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
        <div class="px-5 py-4 border-b border-slate-100 bg-green-50/40 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <h3 class="text-sm font-semibold text-slate-900">Selecciona el rol que deseas asignar al nuevo usuario</h3>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('super-admin.usuarios-sistema.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nombres <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                               class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Apellidos <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
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
                            <option value="">Selecciona un tipo...</option>
                            @foreach(\App\Enums\TipoDocumentoEnum::cases() as $tipo)
                                <option value="{{ $tipo->value }}" {{ old('tipo_documento') === $tipo->value ? 'selected' : '' }}>
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
                        <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" required
                               class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('numero_documento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Correo electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rol" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Rol
                        </label>
                        <select name="rol" id="rol"
                                class="w-full border @error('rol') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">— Sin rol por ahora —</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}" {{ old('rol') === $rol->name ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required
                               class="w-full border @error('password') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirmar contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label for="training_center_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Centro de formación <span class="text-slate-400 font-normal">(requerido según el rol)</span>
                        </label>
                        <select name="training_center_id" id="training_center_id"
                                class="w-full border @error('training_center_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">— Sin centro asignado —</option>
                            @foreach($centros as $centro)
                                <option value="{{ $centro->id }}" {{ old('training_center_id') == $centro->id ? 'selected' : '' }}>
                                    {{ $centro->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('training_center_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-500 mt-1">
                            Los roles de director, líder, asesor e investigador requieren un centro de formación.
                        </p>
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
                        <p class="text-xs text-slate-500 mt-0.5">Se enviará al usuario su número de documento y contraseña al correo registrado.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="submit"
                            class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">
                        Crear usuario
                    </button>
                    <a href="{{ route('super-admin.usuarios-sistema.index') }}"
                       class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg text-sm transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
