<x-app-layout>
    <x-slot name="header">Editar Investigador</x-slot>

    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
            <a href="{{ route('director.investigadores.index') }}" class="hover:text-[#39A900]">Investigadores</a>
            <span>/</span>
            <span class="text-slate-800 font-medium">Editar</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Editar Investigador</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Actualiza los datos personales de
            <span class="font-medium text-slate-700">
                {{ $investigador->person?->primer_nombre ?? $investigador->email }}
                {{ $investigador->person?->primer_apellido ?? '' }}
            </span>.
        </p>
    </div>
w
                <div class="w-10 h-10 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-[#39A900]">
                        {{ strtoupper(substr($investigador->person?->primer_nombre ?? $investigador->email ?? 'U', 0, 1)) }}{{ strtoupper(substr($investigador->person?->primer_apellido ?? '', 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Datos personales</h2>
                    <p class="text-xs text-slate-500">{{ $investigador->email }}</p>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('director.investigadores.update', $investigador) }}"
                  class="p-6 space-y-5">
                @csrf
                @method('PUT')

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Información de solo lectura --}}
                <div class="bg-slate-50 border border-slate-100 rounded-lg px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Documento</p>
                        <p class="text-sm text-slate-800 font-medium mt-0.5">
                            {{ ucwords(str_replace('_', ' ', $investigador->tipo_documento ?? '')) }}
                            &mdash;
                            {{ $investigador->numero_documento ?? '—' }}
                        </p>
                    </div>
                    <div class="text-xs text-slate-400 bg-white border border-slate-200 px-2.5 py-1.5 rounded-md">
                        Solo lectura
                    </div>
                </div>

                {{-- Nombres --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Primer nombre <span class="text-red-400">*</span>
                        </label>
                        <input type="text"
                               name="primer_nombre"
                               id="primer_nombre"
                               value="{{ old('primer_nombre', $investigador->person?->primer_nombre) }}"
                               placeholder="Primer nombre"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_nombre') border-red-400 @enderror">
                        @error('primer_nombre')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo nombre</label>
                        <input type="text"
                               name="segundo_nombre"
                               id="segundo_nombre"
                               value="{{ old('segundo_nombre', $investigador->person?->segundo_nombre) }}"
                               placeholder="Segundo nombre (opcional)"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                </div>

                {{-- Apellidos --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Primer apellido <span class="text-red-400">*</span>
                        </label>
                        <input type="text"
                               name="primer_apellido"
                               id="primer_apellido"
                               value="{{ old('primer_apellido', $investigador->person?->primer_apellido) }}"
                               placeholder="Primer apellido"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_apellido') border-red-400 @enderror">
                        @error('primer_apellido')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo apellido</label>
                        <input type="text"
                               name="segundo_apellido"
                               id="segundo_apellido"
                               value="{{ old('segundo_apellido', $investigador->person?->segundo_apellido) }}"
                               placeholder="Segundo apellido (opcional)"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                </div>

                {{-- Link CvLAC --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Link CvLAC</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                            </svg>
                        </div>
                        <input type="url"
                               name="cvlac_link"
                               id="cvlac_link"
                               value="{{ old('cvlac_link', $investigador->person?->cvlac_link) }}"
                               placeholder="https://scienti.minciencias.gov.co/cvlac/..."
                               class="w-full border border-slate-200 rounded-lg pl-10 pr-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('cvlac_link') border-red-400 @enderror">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Enlace al perfil CvLAC del investigador en Minciencias.</p>
                    @error('cvlac_link')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Aviso de campos no editables --}}
                <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-700">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <span>
                        El correo institucional, tipo y número de documento no son editables desde aquí.
                        Para cambios en esos campos, contacta al Administrador del Sistema.
                        Si necesitas restablecer la contraseña, usa la opción correspondiente en el listado de investigadores.
                    </span>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">
                        Guardar cambios
                    </button>
                    <a href="{{ route('director.investigadores.index') }}"
                       class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
