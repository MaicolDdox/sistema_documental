@extends('layouts.sgd')

@section('title', 'Nuevo Semillero')
@section('header', '')

@section('content')
{{-- Breadcrumbs --}}
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <a href="{{ route('dir-sem.semilleros.index') }}" class="hover:text-[#39A900]">Semilleros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Nuevo Semillero</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-1">Nuevo Semillero</h2>
<p class="text-sm text-slate-500 mb-6">Completa los datos para registrar un nuevo semillero en tu centro de formación.</p>

<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900">Información del Semillero</h3>
            <p class="text-xs text-slate-500 mt-1">Los campos marcados con <span class="text-red-500">*</span> son obligatorios.</p>
        </div>

        <form action="{{ route('dir-sem.semilleros.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <div class="space-y-5">
                {{-- Nombre --}}
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Semillero <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           placeholder="Ej: Semillero de Desarrollo de Software"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('nombre') border-red-300 @enderror">
                    @error('nombre')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Código (opcional, se autocompleta si se deja vacío) --}}
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                        <input type="number" name="codigo" id="codigo" value="{{ old('codigo', $siguienteCodigo ?? '') }}" min="1" step="1"
                               placeholder="{{ $siguienteCodigo ?? 'Auto' }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('codigo') border-red-300 @enderror">
                        <p class="text-xs text-slate-500 mt-1">Si se deja vacío se asignará el siguiente número disponible.</p>
                        @error('codigo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grupo de investigación --}}
                    <div>
                        <label for="research_group_id" class="block text-sm font-medium text-slate-700 mb-1.5">Grupo de Investigación</label>
                        <select name="research_group_id" id="research_group_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10 @error('research_group_id') border-red-300 @enderror">
                            <option value="">Ninguno</option>
                            @foreach($gruposInvestigacion ?? [] as $grupo)
                                <option value="{{ $grupo->id }}" {{ old('research_group_id') == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->nombre }} @if($grupo->codigo)({{ $grupo->codigo }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('research_group_id')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Logo --}}
                <div>
                    <label for="logo" class="block text-sm font-medium text-slate-700 mb-1.5">Logo del semillero (opcional)</label>
                    <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('logo') border-red-300 @enderror">
                    <p class="text-xs text-slate-500 mt-1">Formatos: JPG, PNG, GIF o WebP. Tamaño máximo: 2 MB.</p>
                    @error('logo')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Líder --}}
                <div>
                    <label for="lider_id" class="block text-sm font-medium text-slate-700 mb-1.5">Líder Asignado (opcional)</label>
                    <select name="lider_id" id="lider_id"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10 @error('lider_id') border-red-300 @enderror">
                        <option value="">Sin líder asignado</option>
                        @foreach($lideres as $lider)
                            @php
                                $nombreLider = $lider->person
                                    ? trim(($lider->person->primer_nombre ?? '') . ' ' . ($lider->person->primer_apellido ?? ''))
                                    : $lider->email;
                                if ($nombreLider === '') {
                                    $nombreLider = $lider->email;
                                }
                            @endphp
                            <option value="{{ $lider->id }}" {{ old('lider_id') == $lider->id ? 'selected' : '' }}>
                                {{ $nombreLider }} ({{ $lider->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('lider_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-500 mt-1">Solo se muestran usuarios activos con rol <strong>Líder de Semillero</strong> de este centro.</p>
                </div>

                {{-- Descripción --}}
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción o enfoque (opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="4" placeholder="Propósito, líneas de investigación, etc."
                              class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('descripcion') border-red-300 @enderror">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('dir-sem.semilleros.index') }}"
                   class="inline-flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg text-sm transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-colors focus:ring-2 focus:ring-[#39A900]/30 outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar Semillero
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
