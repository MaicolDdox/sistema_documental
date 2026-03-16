<x-app-layout>
<x-slot name="header">Editar Proyecto</x-slot>

<div class="max-w-3xl" x-data="{ tienesMacro: {{ old('tiene_macroproyecto', $proyecto->vinculacion_macro_proyecto ? '1' : '0') }} == 1 }">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="mb-5 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
            ⚠️ El <strong>tipo de investigación</strong> no puede modificarse después de la creación del proyecto.
        </div>

        <form method="POST" action="{{ route('asesor.proyectos.update', $proyecto->id) }}" novalidate>
            @csrf
            @method('PUT')

            {{-- Info básica --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Información básica</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del proyecto <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $proyecto->nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('nombre') border-red-400 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripccion" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all resize-none">{{ old('descripccion', $proyecto->descripccion) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Clasificación --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Clasificación</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Línea de investigación <span class="text-red-500">*</span></label>
                        <select name="research_line_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('research_line_id') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach($lineasInves as $l)
                                <option value="{{ $l->id }}" {{ old('research_line_id', $proyecto->research_line_id) == $l->id ? 'selected' : '' }}>{{ $l->nombre }}</option>
                            @endforeach
                        </select>
                        @error('research_line_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Línea tecnológica</label>
                        <select name="technological_line_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Ninguna</option>
                            @foreach($lineasTec as $lt)
                                <option value="{{ $lt->id }}" {{ old('technological_line_id', $proyecto->technological_line_id) == $lt->id ? 'selected' : '' }}>{{ $lt->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Área temática</label>
                        <select name="thematic_area_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Ninguna</option>
                            @foreach($areasTematicas as $at)
                                <option value="{{ $at->id }}" {{ old('thematic_area_id', $proyecto->thematic_area_id) == $at->id ? 'selected' : '' }}>{{ $at->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Modalidad del proyecto <span class="text-red-500">*</span></label>
                        <select name="project_modality_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('project_modality_id') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach($modalidades as $m)
                                <option value="{{ $m->id }}" {{ old('project_modality_id', $proyecto->project_modality_id) == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                        @error('project_modality_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1 text-slate-400">Tipo de investigación (bloqueado)</label>
                        <input type="text" value="{{ $proyecto->investigationType?->nombre ?? '—' }}" disabled
                               class="w-full border border-slate-100 rounded-lg px-3 py-2.5 text-sm text-slate-400 bg-slate-50">
                        <input type="hidden" name="investigation_type_id" value="{{ $proyecto->investigation_type_id }}">
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Fechas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de inicio <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $proyecto->fecha_inicio) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('fecha_inicio') border-red-400 @enderror">
                        @error('fecha_inicio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de fin</label>
                        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $proyecto->fecha_fin) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @error('fecha_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Macroproyecto --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Vinculación a Macroproyecto</h3>
                <div class="flex items-center gap-3 mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="tiene_macroproyecto" value="0">
                        <input type="checkbox" name="tiene_macroproyecto" value="1"
                               class="sr-only peer"
                               {{ old('tiene_macroproyecto', $proyecto->vinculacion_macro_proyecto) ? 'checked' : '' }}
                               @change="tienesMacro = $event.target.checked">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#39A900]"></div>
                    </label>
                    <span class="text-sm text-slate-700">¿Vinculado a macroproyecto?</span>
                </div>
                <div x-show="tienesMacro" class="grid grid-cols-1 gap-4" style="{{ $proyecto->vinculacion_macro_proyecto ? '' : 'display:none' }}">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Seleccionar Macroproyecto <span class="text-red-500">*</span></label>
                        <select name="macro_project_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('macro_project_id') border-red-400 @enderror">
                            <option value="">Seleccione un macroproyecto...</option>
                            @foreach($macroProyectos as $mp)
                                <option value="{{ $mp->id }}" {{ old('macro_project_id', $proyecto->macro_project_id) == $mp->id ? 'selected' : '' }}>
                                    [{{ $mp->codigo }}] {{ $mp->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('macro_project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90" style="background:#39A900">
                    Guardar cambios
                </button>
                <a href="{{ route('asesor.proyectos.show', $proyecto->id) }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
