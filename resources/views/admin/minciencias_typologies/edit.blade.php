<x-app-layout>
    <x-slot name="header">Editar Tipología Minciencias</x-slot>
    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.minciencias-typologies.index') }}" class="hover:text-slate-700">Tipologías Minciencias</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Editar</span>
    </nav>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Editar Tipología</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $minciencias_typology->nombre }}</p>
        </div>
        <a href="{{ route('admin.minciencias-typologies.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm">Volver</a>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 max-w-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50"><h3 class="text-sm font-semibold text-slate-900">Datos</h3></div>
        <form method="POST" action="{{ route('admin.minciencias-typologies.update', $minciencias_typology) }}" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $minciencias_typology->nombre) }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="codigo" class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $minciencias_typology->codigo) }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @error('codigo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="descripccion" id="descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion', $minciencias_typology->descripccion) }}</textarea>
                @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.minciencias-typologies.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
            </div>
        </form>
    </div>
</x-app-layout>
