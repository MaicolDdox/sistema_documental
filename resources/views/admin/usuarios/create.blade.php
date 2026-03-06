<x-app-layout>
    <x-slot name="header">Crear Usuario</x-slot>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Crear Usuario</h2>
        <p class="text-sm text-slate-500 mt-1">Ingresa los datos para registrar un nuevo usuario en el sistema.</p>
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
        <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                           class="w-full border @error('apellido') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('apellido')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Numero de Documento -->
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Número de Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" required
                           class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('numero_documento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña Temporal <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required
                           class="w-full border @error('password') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <p class="text-xs text-slate-500 mt-1">Mínimo 8 caracteres.</p>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rol -->
                <div>
                    <label for="rol" class="block text-sm font-medium text-slate-700 mb-1.5">Rol Inicial <span class="text-red-500">*</span></label>
                    <select name="rol" id="rol" required
                            class="w-full border @error('rol') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        <option value="">Selecciona un rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('rol') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
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
