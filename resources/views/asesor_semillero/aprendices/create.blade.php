<x-app-layout>
<x-slot name="header">Registrar Nuevo Aprendiz</x-slot>

<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <p class="text-sm text-slate-500 mb-6">
            El aprendiz quedará registrado con <strong>estado inactivo</strong> — no tendrá acceso al sistema. Es un registro documental del semillero.
        </p>

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm font-medium">
                Por favor, corrige los errores en el formulario para continuar.
            </div>
        @endif

        <form method="POST" action="{{ route('asesor.aprendices.store') }}" novalidate>
            @csrf

            {{-- Datos personales --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos personales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primer nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_nombre" value="{{ old('primer_nombre') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_nombre') border-red-400 @enderror">
                        @error('primer_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Segundo nombre</label>
                        <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('segundo_nombre') border-red-400 @enderror">
                        @error('segundo_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primer apellido <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_apellido" value="{{ old('primer_apellido') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_apellido') border-red-400 @enderror">
                        @error('primer_apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Segundo apellido</label>
                        <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('segundo_apellido') border-red-400 @enderror">
                        @error('segundo_apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                        <select name="tipo_documento" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('tipo_documento') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            <option value="cedula ciudadana" {{ old('tipo_documento') == 'cedula ciudadana' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                            <option value="documento identidad" {{ old('tipo_documento') == 'documento identidad' ? 'selected' : '' }}>Documento de Identidad</option>
                            <option value="pasaporte" {{ old('tipo_documento') == 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                            <option value="cedula extrangera" {{ old('tipo_documento') == 'cedula extrangera' ? 'selected' : '' }}>Cédula Extranjera</option>
                        </select>
                        @error('tipo_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                        <input type="number" name="numero_documento" value="{{ old('numero_documento') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('numero_documento') border-red-400 @enderror">
                        @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Género <span class="text-red-500">*</span></label>
                        <select name="genero" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('genero') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            <option value="masculino" {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                            <option value="prefiero no decirlo" {{ old('genero') == 'prefiero no decirlo' ? 'selected' : '' }}>Prefiero no decirlo</option>
                        </select>
                        @error('genero') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">EPS <span class="text-red-500">*</span></label>
                        <input type="text" name="eps" value="{{ old('eps') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('eps') border-red-400 @enderror">
                        @error('eps') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Celular <span class="text-red-500">*</span></label>
                        <input type="number" name="celular" value="{{ old('celular') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('celular') border-red-400 @enderror">
                        @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono fijo</label>
                        <input type="number" name="telefono" value="{{ old('telefono') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('telefono') border-red-400 @enderror">
                        @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Datos académicos --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos académicos e institucionales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo institucional <span class="text-red-500">*</span></label>
                        <input type="email" name="email_institucional" value="{{ old('email_institucional') }}" placeholder="aprendiz@sena.edu.co"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('email_institucional') border-red-400 @enderror">
                        @error('email_institucional') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cargo / Rol <span class="text-red-500">*</span></label>
                        <select name="entity_position_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('entity_position_id') border-red-400 @enderror">
                            <option value="">Seleccionar cargo...</option>
                            @foreach($cargos as $grupo => $lista)
                                <optgroup label="{{ $grupo }}">
                                    @foreach($lista as $cargo)
                                        <option value="{{ $cargo->id }}" {{ old('entity_position_id') == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('entity_position_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de vinculación <span class="text-red-500">*</span></label>
                        <select name="linkage_type_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('linkage_type_id') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach($tiposVinculacion as $tv)
                                <option value="{{ $tv->id }}" {{ old('linkage_type_id') == $tv->id ? 'selected' : '' }}>{{ $tv->nombre }}</option>
                            @endforeach
                        </select>
                        @error('linkage_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Programa de formación <span class="text-red-500">*</span></label>
                        <select name="training_program_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('training_program_id') border-red-400 @enderror">
                            <option value="">Seleccionar programa...</option>
                            @foreach($programasFormacion as $pf)
                                <option value="{{ $pf->id }}" {{ old('training_program_id') == $pf->id ? 'selected' : '' }}>
                                    {{ $pf->nombre }} @if($pf->trainingProgramType)({{ $pf->trainingProgramType->nombre }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('training_program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
                        style="background:#39A900">
                    Registrar Aprendiz
                </button>
                <a href="{{ route('asesor.aprendices.index') }}"
                   class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
