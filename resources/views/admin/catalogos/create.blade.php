<x-app-layout>
    <x-slot name="header">Crear Catálogo</x-slot>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Crear Catálogo</h2>
        <p class="text-sm text-slate-500 mt-1">Registra una nueva tipología o parámetro para el sistema.</p>
    </div>
    <a href="{{ route('admin.catalogos.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all hidden sm:inline-block">
        Volver al listado
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <h3 class="text-sm font-semibold text-slate-900">Detalles del Catálogo</h3>
    </div>
    
    <div class="p-5">
        <form method="POST" action="{{ route('admin.catalogos.store') }}" class="space-y-5">
            @csrf

            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Catálogo <span class="text-red-500">*</span></label>
                <select name="tipo" id="tipo" required
                        class="w-full border @error('tipo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Selecciona el tipo...</option>
                    <option value="area_conocimiento" {{ old('tipo') == 'area_conocimiento' ? 'selected' : '' }}>Área de Conocimiento</option>
                    <option value="tipo_proyecto" {{ old('tipo') == 'tipo_proyecto' ? 'selected' : '' }}>Tipo de Proyecto</option>
                    <option value="programa_formacion" {{ old('tipo') == 'programa_formacion' ? 'selected' : '' }}>Programa de Formación</option>
                    <option value="linea_investigacion" {{ old('tipo') == 'linea_investigacion' ? 'selected' : '' }}>Línea de Investigación</option>
                    <option value="red_conocimiento" {{ old('tipo') == 'red_conocimiento' ? 'selected' : '' }}>Red de Conocimiento</option>
                    <option value="tipo_documento" {{ old('tipo') == 'tipo_documento' ? 'selected' : '' }}>Tipo de Documento</option>
                    <option value="estado_proyecto" {{ old('tipo') == 'estado_proyecto' ? 'selected' : '' }}>Estado de Proyecto</option>
                </select>
                @error('tipo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                       class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full border @error('descripcion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-[#39A900] bg-white border-slate-300 rounded focus:ring-[#39A900]/50 focus:ring-2 transition-all cursor-pointer">
                </div>
                <div class="ml-3 text-sm">
                    <label for="activo" class="font-medium text-slate-700 cursor-pointer">Estado Activo</label>
                    <p class="text-slate-500">Si está desmarcado, el catálogo no aparecerá en las listas de selección del sistema.</p>
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.catalogos.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Guardar Catálogo
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
