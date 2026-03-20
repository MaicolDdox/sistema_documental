@extends('layouts.sgd')

@section('title', 'Registrar producto para grupo de investigación')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Registrar producto para grupo de investigación</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        Grupo: <span class="font-semibold">{{ $grupo->nombre }}</span> — Semillero: {{ $semillero->nombre }}
    </p>
</div>

@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm space-y-1">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('lider-sem.productos.grupo.store') }}" class="space-y-6">
    @csrf

    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Datos del producto</h3>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Proyecto asociado <span class="text-red-500">*</span></label>
                <select name="project_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                        required>
                    <option value="">Selecciona un proyecto...</option>
                    @foreach($proyectos as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Título del producto <span class="text-red-500">*</span></label>
                <input type="text" name="titulo" value="{{ old('titulo') }}"
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <textarea name="descripccion" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">{{ old('descripccion') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Año de publicación <span class="text-red-500">*</span></label>
                    <input type="number" name="anio_publicacion"
                           value="{{ old('anio_publicacion', now()->year) }}"
                           min="2000" max="{{ now()->year + 2 }}"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                           required>
                </div>

                <div x-data="{ tipoOrigen: '{{ old('tipo_proyecto_origen', 'SEMILLEROS') }}' }" class="md:col-span-2 bg-slate-50 border border-slate-100 p-4 rounded-xl">
                    <h4 class="text-sm font-semibold text-slate-800 mb-3">Origen del producto</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de proyecto de origen <span class="text-red-500">*</span></label>
                            <select name="tipo_proyecto_origen" x-model="tipoOrigen"
                                    class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all" required>
                                <option value="">Selecciona...</option>
                                @foreach(\App\Enums\TipoProyectoOrigenEnum::cases() as $tipo)
                                    <option value="{{ $tipo->value }}">{{ $tipo->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="tipoOrigen === 'otro'" x-transition>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">¿Cuál? <span class="text-red-500">*</span></label>
                            <input type="text" name="campo_otro" value="{{ old('campo_otro') }}"
                                   :required="tipoOrigen === 'otro'"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>

                        <div x-show="tipoOrigen && tipoOrigen !== 'formativo_sena' && tipoOrigen !== 'otro'" x-transition>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Código del proyecto origen <span class="text-xs text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="text" name="codigo_proyecto_origen" value="{{ old('codigo_proyecto_origen') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del programa de formación que impacta <span class="text-xs text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="text" name="nombre_programa_formacion_impacto" value="{{ old('nombre_programa_formacion_impacto') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Clasificación Minciencias</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipología Minciencias</label>
                <select name="minciencias_typology_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Selecciona...</option>
                    @foreach($tipologias as $t)
                        <option value="{{ $t->id }}" {{ old('minciencias_typology_id') == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Subcategoría Minciencias</label>
                <select name="minciencias_subcategory_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Selecciona...</option>
                    @foreach($subcategorias as $s)
                        <option value="{{ $s->id }}" {{ old('minciencias_subcategory_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gran área del conocimiento</label>
                <select name="knowledge_grand_area_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Selecciona...</option>
                    @foreach($grandesAreas as $ga)
                        <option value="{{ $ga->id }}" {{ old('knowledge_grand_area_id') == $ga->id ? 'selected' : '' }}>{{ $ga->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Área del conocimiento</label>
                <select name="knowledge_area_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Selecciona...</option>
                    @foreach($areasConocimiento as $ac)
                        <option value="{{ $ac->id }}" {{ old('knowledge_area_id') == $ac->id ? 'selected' : '' }}>{{ $ac->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Repositorio y publicación</h3>
        </div>
        <div class="p-5 space-y-3" x-data="{ tieneRepo: {{ old('tiene_repositorio') ? 'true' : 'false' }} }">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="tiene_repositorio" value="1" x-model="tieneRepo"
                       class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]"
                       {{ old('tiene_repositorio') ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">El producto tiene URL de repositorio</span>
            </label>
            <div x-show="tieneRepo" x-transition>
                <input type="url" name="url_repositorio" value="{{ old('url_repositorio') }}"
                       placeholder="https://repositorio.sena.edu.co/..."
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="autoriza_datos" value="1"
                       class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]"
                       {{ old('autoriza_datos') ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Autorizo el uso de datos del producto para informes del grupo</span>
            </label>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Registrar producto en el grupo
        </button>
        <a href="{{ route('lider-sem.productos') }}"
           class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Cancelar
        </a>
    </div>
</form>
@endsection

