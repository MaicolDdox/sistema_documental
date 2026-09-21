@extends('layouts.sgd')

@section('title', 'Información del Grupo')
@section('header', '')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $grupo->nombre }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Código {{ $grupo->codigo }} — completa la información de tu grupo de investigación.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('director-grupo-investigacion.grupo.update') }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4"
                          class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripcion', $grupo->descripcion) }}</textarea>
                @error('descripcion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="logo" class="block text-sm font-medium text-slate-700 mb-1.5">Logo (URL)</label>
                <input type="text" name="logo" id="logo" value="{{ old('logo', $grupo->logo) }}"
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @error('logo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="linea_investigacion_principal_id" class="block text-sm font-medium text-slate-700 mb-1.5">Línea de investigación principal</label>
                <select name="linea_investigacion_principal_id" id="linea_investigacion_principal_id"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    <option value="">Sin especificar</option>
                    @foreach($lineasInvestigacion as $linea)
                        <option value="{{ $linea->id }}" {{ (string) old('linea_investigacion_principal_id', $grupo->linea_investigacion_principal_id) === (string) $linea->id ? 'selected' : '' }}>{{ $linea->nombre }}</option>
                    @endforeach
                </select>
                @error('linea_investigacion_principal_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
