@extends('layouts.sgd')

@section('title', 'Detalle del Producto')
@section('header', '')

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Producto en revisión</h1>
        <p class="text-sm text-slate-500 mt-0.5">Ver detalle · Descargar / abrir evidencia · Aprobar / Rechazar</p>
    </div>
    <a href="{{ route('lider-sem.productos') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a Productos
    </a>
@php
    $estadoRev = $producto->estado_revision->value ?? (is_string($producto->estado_revision) ? $producto->estado_revision : 'pendiente');
    $tipoNombre = $producto->mincienciasTypology?->nombre ?? '—';
    $autorNombre = $producto->author?->person?->nombre_completo ?? $producto->author?->email ?? '—';
    $proyectoNombre = $producto->product?->project?->nombre ?? '—';
    $repositorioUrl = $producto->url_repositorio;
    $evidenciaPath = $producto->evidencia;
@endphp
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-2">{{ $producto->titulo ?? '—' }}</h2>
            <p class="text-xs text-slate-500 mb-4">Proyecto: {{ $proyectoNombre }}</p>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Tipo de producto</dt>
                    <dd class="text-slate-800 mt-0.5">{{ $tipoNombre }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Autor</dt>
                    <dd class="text-slate-800 mt-0.5">{{ $autorNombre }}@if($producto->es_mio) (tú) @endif</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Estado de revisión</dt>
                    <dd class="mt-1">
                        @if($estadoRev === 'pendiente')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                        @elseif($estadoRev === 'en_revision')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">En revisión</span>
                        @elseif($estadoRev === 'aprobado')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @endif
                    </dd>
                </div>
                @if($producto->observaciones_revision)
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Última observación</dt>
                    <dd class="mt-1 text-slate-800 whitespace-pre-line text-sm border border-slate-100 rounded-xl px-3 py-2 bg-slate-50">{{ $producto->observaciones_revision }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Evidencia del producto</h3>
            @if($repositorioUrl || $evidenciaPath)
                <div class="space-y-3">
                    @if($repositorioUrl)
                        <a href="{{ $repositorioUrl }}" target="_blank"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5M10.172 13.828a4 4 0 010-5.656l3-3a4 4 0 115.656 5.656l-1.5 1.5"/></svg>
                            Abrir repositorio
                        </a>
                    @endif
                    @if($evidenciaPath)
                        <a href="{{ Storage::url($evidenciaPath) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                            Descargar / previsualizar archivo
                        </a>
                        <p class="text-xs text-slate-500">El archivo se abre en una nueva pestaña si el navegador lo soporta (PDF), o se descargará.</p>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-500">Este producto aún no tiene evidencia asociada.</p>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @if(!$producto->es_mio && in_array($estadoRev, ['pendiente', 'en_revision'], true))
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Decisión del líder</h3>
            <p class="text-xs text-slate-500 mb-3">Solo puedes decidir sobre productos de tu semillero donde no eres el autor.</p>

            <form action="{{ route('lider-sem.productos.aprobar', $producto->id) }}" method="post" class="space-y-3 mb-4">
                @csrf
                @method('PATCH')
                <label class="block text-xs font-medium text-slate-700 mb-1">Observaciones (opcional)</label>
                <textarea name="observaciones" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]" placeholder="Comentario breve de aprobación..."></textarea>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">
                    ✓ Aprobar producto
                </button>
            </form>

            <form action="{{ route('lider-sem.productos.rechazar', $producto->id) }}" method="post" class="space-y-3">
                @csrf
                @method('PATCH')
                <label class="block text-xs font-medium text-slate-700 mb-1">Observaciones de rechazo *</label>
                <textarea name="observaciones" rows="3" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400" placeholder="Explica por qué se rechaza y qué debe corregir el autor..."></textarea>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    ✗ Rechazar producto
                </button>
            </form>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-2">Decisión cerrada</h3>
            <p class="text-xs text-slate-500">
                @if($producto->es_mio)
                    No puedes aprobar o rechazar tus propios productos.
                @else
                    Este producto ya no está en estado pendiente/en revisión.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

