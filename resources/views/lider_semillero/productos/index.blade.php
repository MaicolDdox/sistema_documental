@extends('layouts.sgd')

@section('title', 'Productos del Semillero')
@section('header', '')

@section('content')
@if(!$semillero)
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Productos</h1>
    <p class="text-sm text-slate-500 mt-0.5">Aprobar · Rechazar · Registrar productos</p>
</div>
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder. No hay productos que mostrar.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
<div x-data="{
    modalAprobar: false,
    modalRechazar: false,
    modalRegistrar: false,
    selectedId: null,
    selectedTitulo: '',
    baseUrl: '{{ url('lider-semillero/productos') }}',
    openAprobar(id, titulo) { this.selectedId = id; this.selectedTitulo = titulo || ''; this.modalAprobar = true; },
    openRechazar(id, titulo) { this.selectedId = id; this.selectedTitulo = titulo || ''; this.modalRechazar = true; }
}" x-init="@if(session('rechazar_id')) $nextTick(() => { selectedId = {{ session('rechazar_id') }}; modalRechazar = true; }); @endif @if($errors->any() && old('titulo') !== null) $nextTick(() => { modalRegistrar = true; }); @endif">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Productos</h1>
        <p class="text-sm text-slate-500 mt-0.5">Aprobar · Rechazar · Registrar productos</p>
    </div>
    <button type="button" @click="modalRegistrar = true" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Registrar Producto
    </button>
</div>
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif
@if($errors->has('observaciones'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ $errors->first('observaciones') }}</div>
@endif

<div class="mb-4">
    <h2 class="text-base font-semibold text-slate-900">Productos del Semillero</h2>
    <p class="text-xs text-slate-500 mt-0.5">Aprobar / Rechazar — Solo productos de TU semillero y NO los tuyos propios</p>
</div>

<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Producto</th>
                    <th class="text-left">Autor / Remitente</th>
                    <th class="text-left">Estado revisión</th>
                    <th class="text-left">Repositorio</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $prod)
                @php
                    $estadoRev = $prod->estado_revision->value ?? (is_string($prod->estado_revision) ? $prod->estado_revision : 'pendiente');
                    $autorNombre = $prod->autor?->person?->nombre_completo ?? $prod->autor?->email ?? '—';
                    $iniciales = $prod->autor && $prod->autor->person
                        ? strtoupper(mb_substr($prod->autor->person->primer_nombre ?? '', 0, 1) . mb_substr($prod->autor->person->primer_apellido ?? '', 0, 1))
                        : ($prod->autor ? strtoupper(mb_substr($prod->autor->email ?? '?', 0, 2)) : '—');
                    $proyectoNombre = $prod->project?->nombre ?? '—';
                    $repositorio = $prod->url_repositorio ? '✓ URL' : ($prod->archivo ? 'Archivo' : '—');
                @endphp
                <tr class="{{ $prod->es_mio ? 'bg-slate-50/80' : '' }}">
                    <td class="px-5 py-3">
                        <div class="font-medium text-slate-800">{{ $prod->nombre ?? '—' }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Proyecto: {{ $proyectoNombre }}</div>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center text-xs font-semibold shrink-0">{{ $iniciales ?: '?' }}</span>
                            <span class="text-slate-700">{{ $autorNombre }}{{ $prod->es_mio ? ' (tú)' : '' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        @if($estadoRev === 'pendiente')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                        @elseif($estadoRev === 'en_revision')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">En revisión</span>
                        @elseif($estadoRev === 'aprobado')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $repositorio }}</td>
                    <td class="px-5 py-3">
                        @if($prod->es_mio)
                        <span class="text-xs text-slate-500 italic">No puedes aprobar los tuyos</span>
                        @else
                        <div class="flex items-center gap-2">
                            <button type="button"
                                data-id="{{ $prod->id }}"
                                data-titulo="{{ e($prod->nombre ?? '') }}"
                                @click="openAprobar($event.currentTarget.dataset.id, $event.currentTarget.dataset.titulo)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition">✓ Aprobar</button>
                            <button type="button"
                                data-id="{{ $prod->id }}"
                                data-titulo="{{ e($prod->nombre ?? '') }}"
                                @click="openRechazar($event.currentTarget.dataset.id, $event.currentTarget.dataset.titulo)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition">✗ Rechazar</button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay productos en este semillero.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Aprobar producto --}}
<template x-teleport="body">
    <div x-show="modalAprobar" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalAprobar = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Aprobar producto</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Solo productos del semillero que lideras</p>
                </div>
                <button type="button" @click="modalAprobar = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 mb-4 text-sm text-green-800" x-show="selectedTitulo">
                <span class="font-medium">✓ Aprobando:</span> <span x-text="selectedTitulo"></span>
            </div>
            <form :action="baseUrl + '/' + selectedId + '/aprobar'" method="post" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones (opcional)</label>
                    <textarea name="observaciones" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]" placeholder="Puedes dejar un comentario de aprobación..."></textarea>
                </div>
                <p class="text-xs text-slate-500">Estado pasará a aprobado. Se registra en productos_grupo_estado_revision.</p>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalAprobar = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">✓ Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</template>

{{-- Modal Rechazar producto --}}
<template x-teleport="body">
    <div x-show="modalRechazar" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalRechazar = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Rechazar producto</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Campo observaciones obligatorio</p>
                </div>
                <button type="button" @click="modalRechazar = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 mb-4 text-sm text-red-800" x-show="selectedTitulo">
                <span class="font-medium">▲ Rechazando:</span> <span x-text="selectedTitulo"></span>
            </div>
            <form :action="baseUrl + '/' + selectedId + '/rechazar'" method="post" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones * obligatorio</label>
                    <textarea name="observaciones" rows="4" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400" placeholder="Describe el motivo del rechazo para que el autor corrija y reenvíe...">{{ old('observaciones') }}</textarea>
                </div>
                <p class="text-xs text-slate-500">Estado pasará a rechazado. El autor puede editar y reenviar cuando vuelva a pendiente.</p>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalRechazar = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700">✗ Confirmar rechazo</button>
                </div>
            </form>
        </div>
    </div>
</template>

{{-- Modal Registrar nuevo producto --}}
<template x-teleport="body">
    <div x-show="modalRegistrar" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalRegistrar = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto"
            @click.stop
            x-data="{ tieneRepositorio: {{ old('tiene_repositorio', 0) ? 'true' : 'false' }} }"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Registrar nuevo producto</h3>
                    <p class="text-xs text-slate-500 mt-0.5">estado_revision inicial = pendiente</p>
                </div>
                <button type="button" @click="modalRegistrar = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('lider-sem.productos.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título del producto *</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="500"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]"
                        placeholder="Ej: Análisis de calidad del agua en zonas rurales">
                    @error('titulo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Proyecto origen *</label>
                    <select name="project_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
                        <option value="">Seleccione...</option>
                        @foreach($proyectosParaRegistro ?? [] as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                    @error('project_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">¿Tiene repositorio en línea?</label>
                    <div class="flex gap-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tiene_repositorio" value="1" @click="tieneRepositorio = true" {{ old('tiene_repositorio') === '1' || old('tiene_repositorio') === 1 ? 'checked' : '' }} class="text-[#39A900] focus:ring-[#39A900]">
                            <span class="text-sm text-slate-700">Sí — URL requerida</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tiene_repositorio" value="0" @click="tieneRepositorio = false" {{ old('tiene_repositorio', '0') !== '1' && old('tiene_repositorio', 0) !== 1 ? 'checked' : '' }} class="text-[#39A900] focus:ring-[#39A900]">
                            <span class="text-sm text-slate-700">No — archivo requerido</span>
                        </label>
                    </div>
                </div>
                <div x-show="tieneRepositorio">
                    <label class="block text-sm font-medium text-slate-700 mb-1">URL del repositorio *</label>
                    <input type="url" name="url_repositorio" value="{{ old('url_repositorio') }}" maxlength="500"
                        :required="tieneRepositorio"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]"
                        placeholder="https://...">
                    @error('url_repositorio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div x-show="!tieneRepositorio">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Archivo del producto *</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-[#39A900]/50 transition-colors">
                        <input type="file" name="evidencia" class="hidden" id="evidencia-registro" accept=".pdf,.doc,.docx,.zip" :required="!tieneRepositorio">
                        <label for="evidencia-registro" class="cursor-pointer flex flex-col items-center gap-2 text-slate-500">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span class="text-sm font-medium">Subir archivo</span>
                        </label>
                    </div>
                    @error('evidencia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl bg-sky-50 border border-sky-200 px-4 py-3 text-sm text-sky-800">
                    El producto se crea con estado_revision = pendiente. Debes ser autor en proyecto_autores del proyecto seleccionado.
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalRegistrar = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">Registrar producto</button>
                </div>
            </form>
        </div>
    </div>
</template>
</div>
@endif
@endsection
