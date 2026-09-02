@php
    $p = $producto ?? null;
    $val = fn ($field, $default = '') => old($field, data_get($p, $field, $default));
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="md:col-span-2">
        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del producto <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" id="nombre" value="{{ $val('nombre') }}" required
               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="3"
                  class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ $val('descripcion') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="training_center_id" class="block text-sm font-medium text-slate-700 mb-1.5">Centro de formación <span class="text-red-500">*</span></label>
        <select name="training_center_id" id="training_center_id" required
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
            <option value="">Selecciona...</option>
            @foreach($trainingCenters as $centro)
                <option value="{{ $centro->id }}" {{ (string) $val('training_center_id') === (string) $centro->id ? 'selected' : '' }}>{{ $centro->nombre }}</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">El administrador de este centro será quien revise y apruebe el producto.</p>
        @error('training_center_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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

    @unless($p)
    <div class="md:col-span-2">
        <label for="archivos" class="block text-sm font-medium text-slate-700 mb-1.5">Archivos adjuntos</label>
        <input type="file" name="archivos[]" id="archivos" multiple
               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        <p class="text-xs text-slate-500 mt-1">Puedes adjuntar varios archivos. También podrás agregar más después de crear el producto.</p>
    </div>
    @endunless
</div>
