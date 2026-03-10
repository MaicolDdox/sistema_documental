@extends('asesor_semillero.layout')

@section('title', 'Editar Aprendiz')
@section('header', 'Editar Aprendiz')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="mb-5 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800">
            ℹ️ El correo institucional se usa como identificador de inicio de sesión. Solo modifícalo si es un error tipográfico.
        </div>

        <form method="POST" action="{{ route('asesor.aprendices.update', $aprendiz->id) }}" novalidate>
            @csrf
            @method('PUT')

            {{-- Datos personales --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos personales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primer nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_nombre" value="{{ old('primer_nombre', $aprendiz->person?->primer_nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_nombre') border-red-400 @enderror">
                        @error('primer_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Segundo nombre</label>
                        <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre', $aprendiz->person?->segundo_nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primer apellido <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_apellido" value="{{ old('primer_apellido', $aprendiz->person?->primer_apellido) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_apellido') border-red-400 @enderror">
                        @error('primer_apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Segundo apellido</label>
                        <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $aprendiz->person?->segundo_apellido) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                        <select name="tipo_documento" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('tipo_documento') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach(['cedula ciudadana','documento identidad','pasaporte','cedula extrangera'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_documento', $aprendiz->tipo_documento?->value) == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                        @error('tipo_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                        <input type="number" name="numero_documento" value="{{ old('numero_documento', $aprendiz->numero_documento) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('numero_documento') border-red-400 @enderror">
                        @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Género <span class="text-red-500">*</span></label>
                        <select name="genero" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('genero') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            <option value="masculino" {{ old('genero', $aprendiz->person?->genero) == 'masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="femenino" {{ old('genero', $aprendiz->person?->genero) == 'femenino' ? 'selected' : '' }}>Femenino</option>
                            <option value="prefiero no decirlo" {{ old('genero', $aprendiz->person?->genero) == 'prefiero no decirlo' ? 'selected' : '' }}>Prefiero no decirlo</option>
                        </select>
                        @error('genero') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">EPS <span class="text-red-500">*</span></label>
                        <input type="text" name="eps" value="{{ old('eps', $aprendiz->person?->eps) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('eps') border-red-400 @enderror">
                        @error('eps') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Celular <span class="text-red-500">*</span></label>
                        <input type="number" name="celular" value="{{ old('celular', $aprendiz->person?->celular) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('celular') border-red-400 @enderror">
                        @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono fijo</label>
                        <input type="number" name="telefono" value="{{ old('telefono', $aprendiz->person?->telefono) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                </div>
            </div>

            {{-- Datos académicos --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos académicos e institucionales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo institucional <span class="text-red-500">*</span></label>
                        <input type="email" name="email_institucional" value="{{ old('email_institucional', $aprendiz->person?->email_institucional) }}"
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
                                        <option value="{{ $cargo->id }}" {{ old('entity_position_id', $aprendiz->person?->entity_position_id) == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
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
                                <option value="{{ $tv->id }}" {{ old('linkage_type_id', $aprendiz->person?->linkage_type_id) == $tv->id ? 'selected' : '' }}>{{ $tv->nombre }}</option>
                            @endforeach
                        </select>
                        @error('linkage_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Programa de formación <span class="text-red-500">*</span></label>
                        <select name="training_program_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('training_program_id') border-red-400 @enderror">
                            <option value="">Seleccionar programa...</option>
                            @foreach($programasFormacion as $pf)
                                <option value="{{ $pf->id }}" {{ old('training_program_id', $aprendiz->person?->training_program_id) == $pf->id ? 'selected' : '' }}>
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
                        style="background:#39A900">Guardar cambios</button>
                <a href="{{ route('asesor.aprendices.index') }}"
                   class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
