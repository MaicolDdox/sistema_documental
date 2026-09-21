@extends('layouts.sgd')

@section('title', 'Editar Grupo de Investigación')
@section('header', '')

@section('content')
<div class="max-w-2xl mx-auto">
    <nav class="text-sm text-slate-500 mb-2">
        <a href="{{ route('admin.grupos-investigacion.index') }}" class="hover:text-[#39A900]">Grupos de Investigación</a>
        <span class="mx-1">/</span>
        <span class="text-slate-700 font-medium">Editar</span>
    </nav>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-4">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900">{{ $grupo->nombre }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Solo puedes editar los datos básicos. La descripción, el logo y la línea de investigación los gestiona el director asignado.</p>
        </div>
        <form action="{{ route('admin.grupos-investigacion.update', $grupo) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Grupo <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $grupo->nombre) }}" required
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="codigo" class="block text-sm font-medium text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $grupo->codigo) }}" maxlength="50" required
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @error('codigo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.grupos-investigacion.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Cancelar</a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
