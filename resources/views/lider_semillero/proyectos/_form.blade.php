@php
    $p = $proyecto ?? null;
    $val = fn ($field, $default = '') => old($field, data_get($p, $field, $default));
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="md:col-span-2">
        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del proyecto <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" id="nombre" value="{{ $val('nombre') }}" required
               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="3"
                  class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ $val('descripcion') }}</textarea>
    </div>

    <div>
        <label for="lider_proyecto_user_id" class="block text-sm font-medium text-slate-700 mb-1.5">Líder de Proyecto <span class="text-red-500">*</span></label>
        <select name="lider_proyecto_user_id" id="lider_proyecto_user_id" required
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Selecciona un líder de proyecto...</option>
            @forelse($lideresProyecto as $lp)
                <option value="{{ $lp->id }}" {{ (string) $val('lider_proyecto_user_id') === (string) $lp->id ? 'selected' : '' }}>
                    {{ $lp->person?->nombre_completo ?? $lp->email }}
                </option>
            @empty
                <option value="" disabled>No has creado ningún Líder de Proyecto todavía</option>
            @endforelse
        </select>
        @error('lider_proyecto_user_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="research_line_id" class="block text-sm font-medium text-slate-700 mb-1.5">Línea de investigación <span class="text-red-500">*</span></label>
        <select name="research_line_id" id="research_line_id" required
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Selecciona...</option>
            @foreach($lineasInvestigacion as $linea)
                <option value="{{ $linea->id }}" {{ (string) $val('research_line_id') === (string) $linea->id ? 'selected' : '' }}>{{ $linea->nombre }}</option>
            @endforeach
        </select>
        @error('research_line_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="technological_line_id" class="block text-sm font-medium text-slate-700 mb-1.5">Línea tecnológica</label>
        <select name="technological_line_id" id="technological_line_id"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            @foreach($lineasTecnologicas as $linea)
                <option value="{{ $linea->id }}" {{ (string) $val('technological_line_id') === (string) $linea->id ? 'selected' : '' }}>{{ $linea->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="thematic_area_id" class="block text-sm font-medium text-slate-700 mb-1.5">Área temática</label>
        <select name="thematic_area_id" id="thematic_area_id"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            @foreach($areasTematicas as $area)
                <option value="{{ $area->id }}" {{ (string) $val('thematic_area_id') === (string) $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="project_modality_id" class="block text-sm font-medium text-slate-700 mb-1.5">Modalidad</label>
        <select name="project_modality_id" id="project_modality_id"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad->id }}" {{ (string) $val('project_modality_id') === (string) $modalidad->id ? 'selected' : '' }}>{{ $modalidad->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="investigation_type_id" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de investigación</label>
        <select name="investigation_type_id" id="investigation_type_id"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            @foreach($tiposInvestigacion as $tipo)
                <option value="{{ $tipo->id }}" {{ (string) $val('investigation_type_id') === (string) $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="tipo_proyecto_origen" class="block text-sm font-medium text-slate-700 mb-1.5">Origen del proyecto</label>
        <select name="tipo_proyecto_origen" id="tipo_proyecto_origen"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            @foreach($tiposOrigen as $origen)
                <option value="{{ $origen->value }}" {{ $val('tipo_proyecto_origen.value', $val('tipo_proyecto_origen')) === $origen->value ? 'selected' : '' }}>{{ ucfirst(strtolower($origen->value)) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="tipo_financiacion" class="block text-sm font-medium text-slate-700 mb-1.5">Financiación</label>
        <select name="tipo_financiacion" id="tipo_financiacion"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Sin especificar</option>
            <option value="capacidad_instalada" {{ $val('tipo_financiacion.value', $val('tipo_financiacion')) === 'capacidad_instalada' ? 'selected' : '' }}>Capacidad instalada</option>
            <option value="financiado" {{ $val('tipo_financiacion.value', $val('tipo_financiacion')) === 'financiado' ? 'selected' : '' }}>Financiado</option>
            <option value="con_alianza" {{ $val('tipo_financiacion.value', $val('tipo_financiacion')) === 'con_alianza' ? 'selected' : '' }}>Con alianza</option>
        </select>
    </div>

    <div>
        <label for="fecha_inicio" class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de inicio</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $val('fecha_inicio') }}"
               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        @error('fecha_inicio') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="fecha_fin" class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de fin estimada</label>
        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $val('fecha_fin') }}"
               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        @error('fecha_fin') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>
</div>
