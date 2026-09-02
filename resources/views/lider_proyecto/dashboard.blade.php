@extends('layouts.sgd')

@section('title', 'Mi Proyecto')
@section('header', '')

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $proyecto->nombre }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Semillero {{ $proyecto->seedling?->nombre ?? '—' }}</p>
    </div>
    <a href="{{ route('lider-proyecto.reporte.descargar') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Descargar reporte
    </a>
</div>

<div class="space-y-6">

    {{-- Card 1: Resumen específico del proyecto --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Resumen del Proyecto</h2>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $proyecto->estado?->value === 'activo' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $proyecto->estado?->value === 'activo' ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-700">{{ $proyecto->descripcion ?? 'Sin descripción registrada.' }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Línea de Investigación</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->researchLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Línea Tecnológica</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->technologicalLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Área Temática</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->thematicArea?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Modalidad</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->projectModality?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Tipo de Investigación</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->investigationType?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Fechas</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? 'N/A' }} → {{ $proyecto->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Aprendices vinculados (solo resumen, sin acciones) --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Aprendices Vinculados ({{ $proyecto->learners->count() }})</h2>
        </div>
        <div class="p-6">
            <div class="space-y-1.5">
                @forelse($proyecto->learners as $aprendiz)
                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-sm">
                    <span class="font-medium text-slate-800">{{ $aprendiz->nombre_completo }}</span>
                    <span class="text-xs text-slate-500">Doc: {{ $aprendiz->numero_documento }} @if($aprendiz->ficha) · Ficha: {{ $aprendiz->ficha }} @endif</span>
                </div>
                @empty
                <p class="text-sm text-slate-400">Sin aprendices registrados.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Card 3: Co-investigadores vinculados (solo resumen, sin acciones) --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Co-investigadores Vinculados ({{ $proyecto->authors->count() }})</h2>
        </div>
        <div class="p-6">
            <div class="space-y-1.5">
                @forelse($proyecto->authors as $coinvestigador)
                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-sm">
                    <span class="font-medium text-slate-800">{{ $coinvestigador->person?->nombre_completo ?? $coinvestigador->email }}</span>
                    <span class="text-xs text-slate-500">{{ $coinvestigador->email }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-400">Sin co-investigadores vinculados.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Estado del producto final --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-3">Estado del producto final</h2>
        @if(!$ultimaEvidenciaProducto)
        <p class="text-sm text-slate-500">Aún no has subido evidencia de producto final. Puedes hacerlo desde <a href="{{ route('lider-proyecto.evidencias.index') }}" class="text-[#39A900] font-medium">Evidencias</a>.</p>
        @else
        @php
            $eLider = $ultimaEvidenciaProducto->estado_revision_lider?->value ?? $ultimaEvidenciaProducto->estado_revision_lider;
            $eDirector = $ultimaEvidenciaProducto->estado_revision_director?->value ?? $ultimaEvidenciaProducto->estado_revision_director;

            if ($eLider === 'rechazado') {
                $trazaTexto = 'Rechazado por el Líder de Semillero — corrige y vuelve a subir';
                $trazaClase = 'bg-red-100 text-red-800';
            } elseif ($eDirector === 'rechazado') {
                $trazaTexto = 'Rechazado por el Director de Semilleros — corrige y vuelve a subir';
                $trazaClase = 'bg-red-100 text-red-800';
            } elseif ($eDirector === 'aprobado') {
                $trazaTexto = 'Aprobado definitivamente por el Director de Semilleros';
                $trazaClase = 'bg-green-100 text-green-800';
            } elseif ($eLider === 'aprobado') {
                $trazaTexto = 'Aprobado por el Líder de Semillero — falta revisión del Director de Semilleros';
                $trazaClase = 'bg-amber-100 text-amber-800';
            } else {
                $trazaTexto = 'Pendiente — ningún revisor lo ha aprobado todavía';
                $trazaClase = 'bg-slate-100 text-slate-600';
            }
        @endphp
        <div class="mb-4">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $trazaClase }}">{{ $trazaTexto }}</span>
        </div>
        <div class="flex items-center gap-6 text-sm">
            <div>
                <p class="text-xs text-slate-500 mb-1">Revisión Líder de Semillero</p>
                @if($eLider === 'aprobado')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                @elseif($eLider === 'rechazado')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado — corrige y vuelve a subir</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Revisión Director de Semilleros</p>
                @if($eDirector === 'aprobado')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado (definitivo)</span>
                @elseif($eDirector === 'rechazado')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado — corrige y vuelve a subir</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Aún no llega a esta etapa</span>
                @endif
            </div>
        </div>
        @if($ultimaEvidenciaProducto->observacion_lider || $ultimaEvidenciaProducto->observacion_director)
        <div class="mt-4 pt-4 border-t border-slate-100 text-sm text-slate-600 space-y-2">
            @if($ultimaEvidenciaProducto->observacion_lider)
                <p><span class="font-medium text-slate-800">Observación del Líder de Semillero:</span> {{ $ultimaEvidenciaProducto->observacion_lider }}</p>
            @endif
            @if($ultimaEvidenciaProducto->observacion_director)
                <p><span class="font-medium text-slate-800">Observación del Director de Semilleros:</span> {{ $ultimaEvidenciaProducto->observacion_director }}</p>
            @endif
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
