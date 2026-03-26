<x-app-layout>
    <x-slot name="header">Nuevo Investigador</x-slot>

    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
            <a href="{{ route('director.investigadores.index') }}" class="hover:text-[#39A900]">Investigadores</a>
            <span>/</span>
            <span class="text-slate-800 font-medium">Nuevo</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Nuevo Investigador</h1>
        <p class="text-sm text-slate-500 mt-0.5">Asigna una contraseña y compártela directamente con el investigador.</p>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Datos del investigador</h2>
            </div>
            <form method="POST" action="{{ route('director.investigadores.store') }}" class="p-6 space-y-5">
                @csrf
                @if(request()->boolean('embedded'))
                    <input type="hidden" name="embedded" value="1">
                @endif

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Documento --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de documento</label>
                        <select name="tipo_documento"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('tipo_documento') border-red-400 @enderror">
                            <option value="">Selecciona...</option>
                            <option value="cedula ciudadana" {{ old('tipo_documento') === 'cedula ciudadana' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                            <option value="tarjeta identidad" {{ old('tipo_documento') === 'tarjeta identidad' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                            <option value="cedula extranjeria" {{ old('tipo_documento') === 'cedula extranjeria' ? 'selected' : '' }}>Cédula de Extranjería</option>
                            <option value="pasaporte" {{ old('tipo_documento') === 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de documento</label>
                        <input type="text" name="numero_documento" value="{{ old('numero_documento') }}"
                               placeholder="0000000000"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('numero_documento') border-red-400 @enderror">
                    </div>
                </div>

                {{-- Nombres --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer nombre <span class="text-red-400">*</span></label>
                        <input type="text" name="primer_nombre" value="{{ old('primer_nombre') }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_nombre') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo nombre</label>
                        <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre') }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                </div>

                {{-- Apellidos --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer apellido <span class="text-red-400">*</span></label>
                        <input type="text" name="primer_apellido" value="{{ old('primer_apellido') }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_apellido') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo apellido</label>
                        <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido') }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                </div>

                {{-- Correo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo institucional <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="nombre@sena.edu.co"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('email') border-red-400 @enderror">
                </div>

                {{-- Contraseña asignada por el director --}}
                <div x-data="{ mostrar: false }">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Contraseña <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input :type="mostrar ? 'text' : 'password'"
                               name="password"
                               id="password"
                               value="{{ old('password') }}"
                               placeholder="Mínimo 8 caracteres"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 pr-11 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('password') border-red-400 @enderror">
                        <button type="button" @click="mostrar = !mostrar"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                            <svg x-show="!mostrar" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="mostrar" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ mostrar2: false }">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Confirmar contraseña <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input :type="mostrar2 ? 'text' : 'password'"
                               name="password_confirmation"
                               placeholder="Repetir contraseña"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 pr-11 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        <button type="button" @click="mostrar2 = !mostrar2"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                            <svg x-show="!mostrar2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="mostrar2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Link CvLAC --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Link CvLAC <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                            </svg>
                        </div>
                        <input type="url" name="cvlac_link" value="{{ old('cvlac_link') }}" required
                               placeholder="https://scienti.minciencias.gov.co/cvlac/..."
                               class="w-full border border-slate-200 rounded-lg pl-10 pr-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('cvlac_link') border-red-400 @enderror">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Enlace al perfil CvLAC del investigador en Minciencias.</p>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">
                        Crear investigador
                    </button>
                    <a href="{{ route('director.investigadores.index') }}"
                       class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@if(request()->boolean('embedded') && session('success'))
    <script>
        if (window.parent && window.parent !== window) {
            window.parent.location.reload();
        }
    </script>
@endif

</x-app-layout>
