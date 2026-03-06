<x-app-layout>
    <x-slot name="header">Editar Programa de Formación</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.training-programs.index') }}" class="hover:text-slate-700">Programas de Formación</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Editar</span>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Editar Programa de Formación</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $training_program->nombre }}</p>
        </div>
        <a href="{{ route('admin.training-programs.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-2xl">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Datos del programa</h3>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('admin.training-programs.update', $training_program) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $training_program->nombre) }}" required
                           class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="ficha" class="block text-sm font-medium text-slate-700 mb-1.5">Ficha <span class="text-red-500">*</span></label>
                    <input type="text" name="ficha" id="ficha" value="{{ old('ficha', $training_program->trainingRecord?->codigo) }}" required placeholder="Ej: 262100"
                           class="w-full border @error('ficha') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('ficha')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                    <input type="text" name="tipo" id="tipo" value="{{ old('tipo', $training_program->trainingProgramType?->nombre) }}" required placeholder="Ej: Técnico"
                           class="w-full border @error('tipo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('tipo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jornada" class="block text-sm font-medium text-slate-700 mb-1.5">Jornada <span class="text-red-500">*</span></label>
                    <select name="jornada" id="jornada" required
                            class="w-full border @error('jornada') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="diurna" {{ old('jornada', $training_program->jornada?->value) == 'diurna' ? 'selected' : '' }}>Diurna</option>
                        <option value="nocturna" {{ old('jornada', $training_program->jornada?->value) == 'nocturna' ? 'selected' : '' }}>Nocturna</option>
                        <option value="presencial" {{ old('jornada', $training_program->jornada?->value) == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    </select>
                    @error('jornada')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="modalidad" class="block text-sm font-medium text-slate-700 mb-1.5">Modalidad <span class="text-red-500">*</span></label>
                    <select name="modalidad" id="modalidad" required
                            class="w-full border @error('modalidad') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="presencial" {{ old('modalidad', $training_program->modalidad?->value) == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="virtual" {{ old('modalidad', $training_program->modalidad?->value) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                    </select>
                    @error('modalidad')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="estado" class="block text-sm font-medium text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                    <select name="estado" id="estado" required
                            class="w-full border @error('estado') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="activo" {{ old('estado', $training_program->estado?->value) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $training_program->estado?->value) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="descripccion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <textarea name="descripccion" id="descripccion" rows="2"
                              class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion', $training_program->descripccion) }}</textarea>
                    @error('descripccion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.training-programs.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
