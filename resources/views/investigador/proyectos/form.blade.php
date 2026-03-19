<x-app-layout>
    <x-slot name="header">{{ $proyecto ? 'Editar Proyecto' : 'Nuevo Proyecto' }}</x-slot>

    {{-- Cuando el formulario se abre embebido en un iframe (modal) y la creación/actualización fue exitosa,
         recargamos la página padre para que cierre el modal y actualice la lista. --}}
    @if(request()->boolean('embedded') && session('success'))
        <script>
            if (window.parent && window.parent !== window) {
                window.parent.location.reload();
            }
        </script>
    @endif

    <div class="max-w-3xl">

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST"
              action="{{ $proyecto ? route('investigador.proyectos.update', $proyecto) : route('investigador.proyectos.store') }}">
            @csrf
            @if($proyecto) @method('PUT') @endif

            {{-- Información básica --}}
            <div class="bg-white rounded-xl border border-slate-200 mb-4">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Información del proyecto</h3>
                </div>
                <div class="p-5 space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del proyecto <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $proyecto?->nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                               placeholder="Título del proyecto de investigación" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                        <textarea name="descripccion" rows="4"
                                  class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                                  placeholder="Describe brevemente los objetivos del proyecto...">{{ old('descripccion', $proyecto?->descripccion) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de inicio</label>
                            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $proyecto?->fecha_inicio?->format('Y-m-d')) }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha fin estimada</label>
                            <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $proyecto?->fecha_fin?->format('Y-m-d')) }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clasificación --}}
            <div class="bg-white rounded-xl border border-slate-200 mb-4">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Clasificación</h3>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Línea de investigación <span class="text-red-500">*</span></label>
                        <select name="research_line_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all" required>
                            <option value="">Selecciona...</option>
                            @foreach($lineasInvestigacion as $linea)
                                <option value="{{ $linea->id }}" {{ old('research_line_id', $proyecto?->research_line_id) == $linea->id ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Línea tecnológica</label>
                        <select name="technological_line_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($lineasTecnologicas as $linea)
                                <option value="{{ $linea->id }}" {{ old('technological_line_id', $proyecto?->technological_line_id) == $linea->id ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Área temática</label>
                        <select name="thematic_area_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($areasTemáticas as $area)
                                <option value="{{ $area->id }}" {{ old('thematic_area_id', $proyecto?->thematic_area_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Modalidad del proyecto</label>
                        <select name="project_modality_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}" {{ old('project_modality_id', $proyecto?->project_modality_id) == $modalidad->id ? 'selected' : '' }}>
                                    {{ $modalidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de investigación</label>
                        <select name="investigation_type_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($tiposInvestigacion as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('investigation_type_id', $proyecto?->investigation_type_id) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Macro-proyecto --}}
            @if($macroProyectos->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 mb-4" x-data="{ vinculado: {{ old('vinculacion_macro_proyecto', $proyecto?->vinculacion_macro_proyecto) ? 'true' : 'false' }} }">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Macro-Proyecto</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Indica si este proyecto forma parte de un macro-proyecto del grupo.</p>
                </div>
                <div class="p-5 space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="vinculacion_macro_proyecto" value="1"
                               x-model="vinculado"
                               class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]"
                               {{ old('vinculacion_macro_proyecto', $proyecto?->vinculacion_macro_proyecto) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Este proyecto está vinculado a un macro-proyecto</span>
                    </label>

                    <div x-show="vinculado" x-transition>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Macro-proyecto del grupo</label>
                        <select name="macro_project_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona el macro-proyecto...</option>
                            @foreach($macroProyectos as $mp)
                                <option value="{{ $mp->id }}" {{ old('macro_project_id', $proyecto?->macro_project_id) == $mp->id ? 'selected' : '' }}>
                                    [{{ $mp->codigo }}] {{ $mp->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @else
            <input type="hidden" name="vinculacion_macro_proyecto" value="0">
            @endif

            {{-- Botones --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    {{ $proyecto ? 'Actualizar proyecto' : 'Crear proyecto' }}
                </button>
                <a href="{{ route('investigador.proyectos.index') }}"
                   class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
