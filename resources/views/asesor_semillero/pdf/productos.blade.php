@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte de Productos de Investigación')
@section('subtitle', 'Listado de productos generados por los proyectos del semillero')

@section('content')

@php
$pendientes  = $productos->where('estado_revision', 'pendiente')->count();
$aprobados   = $productos->where('estado_revision', 'aprobado')->count();
$rechazados  = $productos->where('estado_revision', 'rechazado')->count();
$total       = $productos->count();
@endphp

{{-- Resumen --}}
<div class="metrics-row">
    <div class="metric-card">
        <span class="metric-label">Total Productos</span>
        <span class="metric-value">{{ $total }}</span>
        <span class="metric-sub">en el período</span>
    </div>
    <div class="metric-card metric-green">
        <span class="metric-label">Aprobados</span>
        <span class="metric-value">{{ $aprobados }}</span>
        <span class="metric-sub">
            @if($total > 0){{ round(($aprobados / $total) * 100) }}% del total @endif
        </span>
    </div>
    <div class="metric-card metric-amber">
        <span class="metric-label">Pendientes</span>
        <span class="metric-value">{{ $pendientes }}</span>
        <span class="metric-sub">esperando revisión</span>
    </div>
    <div class="metric-card metric-red">
        <span class="metric-label">Rechazados</span>
        <span class="metric-value">{{ $rechazados }}</span>
        <span class="metric-sub">requieren corrección</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Con Archivo</span>
        <span class="metric-value">{{ $productos->filter(fn($p) => $p->archivo)->count() }}</span>
        <span class="metric-sub">o repositorio</span>
    </div>
</div>

@if($productos->isEmpty())
    <p class="text-muted" style="text-align:center; padding:20px 0">No hay productos registrados en el período seleccionado.</p>
@else
    <div class="section-title">Detalle de Productos</div>
    <div class="section-subtitle">Ordenados por fecha de carga (más reciente primero).</div>

    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width:22%">Nombre del Producto</th>
                <th>Proyecto Asociado</th>
                <th>Estado Proyecto</th>
                <th>Estado Revisión</th>
                <th style="text-align:center">Autores</th>
                <th>Disponibilidad</th>
                <th>Fecha de Carga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $prod)
            @php
                $estado     = $prod->estado_revision ?? 'pendiente';
                $badgeProd  = $estado === 'aprobado' ? 'badge-green' : ($estado === 'rechazado' ? 'badge-red' : 'badge-amber');
                $estadoProy = $prod->project?->estado?->value ?? 'activo';
            @endphp
            <tr>
                <td>
                    <span class="text-bold">{{ $prod->nombre }}</span>
                    @if($prod->anio_publicacion)
                        <br><span class="text-muted text-small">Publicado: {{ $prod->anio_publicacion }}</span>
                    @endif
                </td>
                <td class="text-small">
                    {{ $prod->project?->nombre ?? '—' }}
                </td>
                <td>
                    <span class="badge {{ $estadoProy === 'activo' ? 'badge-green' : 'badge-slate' }}">
                        {{ ucfirst($estadoProy) }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $badgeProd }}">{{ ucfirst($estado) }}</span>
                </td>
                <td style="text-align:center">
                    <span class="badge badge-purple">{{ $prod->productAuthors->count() }}</span>
                    @foreach($prod->productAuthors->take(3) as $pa)
                        @php $nombre = $pa->projectAuthor?->user?->person; @endphp
                        @if($nombre)
                        <span class="text-muted" style="display:block; font-size:8px">
                            {{ $nombre->primer_nombre }} {{ $nombre->primer_apellido }}
                        </span>
                        @endif
                    @endforeach
                    @if($prod->productAuthors->count() > 3)
                        <span style="font-size:8px; color:#94a3b8">+{{ $prod->productAuthors->count() - 3 }} más</span>
                    @endif
                </td>
                <td class="text-small">
                    @if($prod->archivo)
                        <span class="pill">📎 Archivo local</span>
                    @endif
                    @if($prod->url_repositorio)
                        <span class="pill">🔗 Repositorio</span>
                    @endif
                    @if(!$prod->archivo && !$prod->url_repositorio)
                        <span class="text-muted">No disponible</span>
                    @endif
                </td>
                <td class="text-small text-muted">{{ $prod->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
