@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte General Consolidado')

@section('content')

@php
    $total_semilleros = $semilleros->count();
    $total_proyectos = $proyectos->count();
    $proyectos_activos = $proyectos->where('estado', 'activo')->count();
    $proyectos_inactivos = $total_proyectos - $proyectos_activos;
    $total_aprendices = $aprendices->count();
    $total_productos = $productos->count();
    $productos_aprobados = $productos->where('estado_revision', 'aprobado')->count();
    $productos_pendientes = $productos->where('estado_revision', 'pendiente')->count();
    $productos_rechazados = $productos->where('estado_revision', 'rechazado')->count();
    $pct_aprobacion = $total_productos > 0 ? round(($productos_aprobados / $total_productos) * 100) : 0;
    $pct_pendientes = $total_productos > 0 ? round(($productos_pendientes / $total_productos) * 100) : 0;
    $pct_rechazados = $total_productos > 0 ? round(($productos_rechazados / $total_productos) * 100) : 0;
@endphp

{{-- 1. Tarjetas de Métricas Ejecutivas --}}
<div class="metrics-row">
    <div class="metric-card">
        <span class="metric-label">Semilleros</span>
        <span class="metric-value">{{ $total_semilleros }}</span>
        <span class="metric-sub">registrados</span>
    </div>
    <div class="metric-card metric-blue">
        <span class="metric-label">Proyectos</span>
        <span class="metric-value">{{ $total_proyectos }}</span>
        <span class="metric-sub">{{ $proyectos_activos }} act. / {{ $proyectos_inactivos }} inact.</span>
    </div>
    <div class="metric-card metric-amber">
        <span class="metric-label">Aprendices</span>
        <span class="metric-value">{{ $total_aprendices }}</span>
        <span class="metric-sub">vinculados</span>
    </div>
    <div class="metric-card metric-purple">
        <span class="metric-label">Productos</span>
        <span class="metric-value">{{ $total_productos }}</span>
        <span class="metric-sub">registrados</span>
    </div>
    <div class="metric-card metric-green">
        <span class="metric-label">Aprobación</span>
        <span class="metric-value">{{ $pct_aprobacion }}%</span>
        <span class="metric-sub">de productos</span>
    </div>
</div>

<div style="clear: both; margin-top: 20px;">
    {{-- Gráfico 1: Estado de Productos --}}
    <div style="width: 48%; float: left;">
        <div class="chart-box">
            <div class="chart-title">Estado de Productos</div>
            @if($total_productos > 0)
                <div class="chart-bar-container">
                    <span class="chart-label">Aprobados ({{ $productos_aprobados }})</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ $pct_aprobacion }}%; background-color: #22c55e;"></div>
                    </div>
                </div>
                <div class="chart-bar-container">
                    <span class="chart-label">Pendientes ({{ $productos_pendientes }})</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ $pct_pendientes }}%; background-color: #fbbf24;"></div>
                    </div>
                </div>
                <div class="chart-bar-container" style="margin-bottom: 0;">
                    <span class="chart-label">Rechazados ({{ $productos_rechazados }})</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ $pct_rechazados }}%; background-color: #ef4444;"></div>
                    </div>
                </div>
            @else
                <div class="empty-state" style="padding: 10px;">No hay productos registrados.</div>
            @endif
        </div>
    </div>

    {{-- Gráfico 2: Proyectos por Semillero --}}
    <div style="width: 48%; float: right;">
        <div class="chart-box">
            <div class="chart-title">Proyectos por Semillero</div>
            @if($total_proyectos > 0 && $semilleros->isNotEmpty())
                @php
                    $chartSemilleros = $semilleros->sortByDesc(function($s) {
                        return ($s->proyectosActivos ?? 0) + ($s->proyectosInactivos ?? 0);
                    })->take(4);
                @endphp
                @foreach($chartSemilleros as $sem)
                    @php
                        $sem_proy = ($sem->proyectosActivos ?? 0) + ($sem->proyectosInactivos ?? 0);
                        $pct_proy = $total_proyectos > 0 ? round(($sem_proy / $total_proyectos) * 100) : 0;
                    @endphp
                    <div class="chart-bar-container" @if($loop->last) style="margin-bottom: 0;" @endif>
                        <span class="chart-label">{{ \Illuminate\Support\Str::limit($sem->nombre, 35) }} ({{ $sem_proy }})</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $pct_proy }}%; background-color: #39A900;"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state" style="padding: 10px;">No hay proyectos registrados.</div>
            @endif
        </div>
    </div>
</div>
<div style="clear: both;"></div>

<div style="page-break-after: always;"></div>

{{-- 2. Sección Semilleros --}}
<h2 class="section-title">1. Listado de Semilleros Activos</h2>
@if($semilleros->isEmpty())
    <div class="empty-state">No hay semilleros registrados en este período.</div>
@else
    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width:22%">Nombre del Semillero</th>
                <th>Código / Estado</th>
                <th>Grupo de Investigación</th>
                <th>Líder del Semillero</th>
                <th style="text-align:center">Miembros</th>
                <th style="text-align:center">Proyectos Activos</th>
                <th style="text-align:center">Proyectos Inactivos</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($semilleros as $sem)
            <tr>
                <td><strong>{{ $sem->nombre }}</strong></td>
                <td>
                    <span class="badge {{ $sem->estado?->value === 'activo' ? 'badge-green' : 'badge-gray' }}">
                        {{ ucfirst($sem->estado?->value ?? '—') }}
                    </span><br>
                    <span class="text-muted" style="font-size:9px;">{{ $sem->codigo ?? 'Sin código' }}</span>
                </td>
                <td>{{ $sem->researchGroup?->nombre ?? '—' }}</td>
                <td>
                    @if($sem->leader)
                        <strong>{{ $sem->leader->person ? trim($sem->leader->person->primer_nombre . ' ' . $sem->leader->person->primer_apellido) : $sem->leader->email }}</strong>
                    @else
                        <span class="text-muted">Sin líder asignado</span>
                    @endif
                </td>
                <td style="text-align:center"><span class="badge badge-purple">{{ $sem->totalMiembros ?? 0 }}</span></td>
                <td style="text-align:center"><span class="badge badge-green">{{ $sem->proyectosActivos ?? 0 }}</span></td>
                <td style="text-align:center"><span class="badge badge-gray">{{ $sem->proyectosInactivos ?? 0 }}</span></td>
                <td style="font-size:9px; color:#64748b;">{{ $sem->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div style="padding-top: 15px;"></div>

{{-- 3. Sección Proyectos --}}
<h2 class="section-title">2. Proyectos Destacados</h2>
@if($proyectos->isEmpty())
    <div class="empty-state">No hay proyectos registrados en este período.</div>
@else
    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width: 30%">Nombre del Proyecto</th>
                <th style="width: 20%">Modalidad / Línea</th>
                <th style="width: 15%">Estado</th>
                <th style="width: 20%">Fechas</th>
                <th style="width: 15%" class="text-center">Productos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proyectos as $proy)
                <tr>
                    <td><strong>{{ $proy->nombre }}</strong></td>
                    <td>
                        <span style="display:block;margin-bottom:2px;font-size:10px;">{{ $proy->projectModality?->nombre ?? '—' }}</span>
                        <span class="text-muted" style="font-size:10px;">{{ $proy->researchLine?->nombre ?? '—' }}</span>
                    </td>
                    <td>
                        @if($proy->estado == 'activo') <span class="badge badge-green">Activo</span>
                        @else <span class="badge badge-red">Inactivo</span> @endif
                    </td>
                    <td>
                        <span style="font-size:10px;display:block;">In: {{ $proy->fecha_inicio ? \Carbon\Carbon::parse($proy->fecha_inicio)->format('d/m/Y') : '—' }}</span>
                        <span style="font-size:10px;display:block;">Fin: {{ $proy->fecha_fin ? \Carbon\Carbon::parse($proy->fecha_fin)->format('d/m/Y') : '—' }}</span>
                    </td>
                    <td class="text-center"><span class="badge badge-gray">{{ $proy->products->count() }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div style="page-break-after: always;"></div>

{{-- 4. Sección Aprendices --}}
<h2 class="section-title">3. Aprendices Vinculados</h2>
@if($aprendices->isEmpty())
    <div class="empty-state">No hay aprendices registrados en este período.</div>
@else
    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width: 25%">Nombre Completo</th>
                <th style="width: 15%">Documento</th>
                <th style="width: 25%">Contacto</th>
                <th style="width: 35%">Programa de Formación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aprendices as $ap)
                @php $p = $ap->person; @endphp
                <tr>
                    <td><strong>{{ $p?->primer_nombre }} {{ $p?->primer_apellido }}</strong><br><span class="text-muted">{{ $p?->genero ?? '—' }}</span></td>
                    <td>{{ $ap->tipo_documento?->value }}<br>{{ $ap->numero_documento }}</td>
                    <td style="font-size: 10px;">
                        <span style="display:block;margin-bottom:2px">{{ $p?->email_institucional ?? '—' }}</span>
                        C: {{ $p?->celular ?? '—' }} | T: {{ $p?->telefono ?? '—' }}
                    </td>
                    <td style="font-size: 10px;">
                        <span style="display:block;margin-bottom:2px"><strong>{{ $p?->trainingProgram?->nombre ?? '—' }}</strong></span>
                        <span class="text-muted">{{ $p?->trainingProgram?->trainingProgramType?->nombre ?? '—' }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div style="padding-top: 15px;"></div>

{{-- 5. Sección Productos --}}
<h2 class="section-title">4. Estado de Productos</h2>
@if($productos->isEmpty())
    <div class="empty-state">No hay productos registrados en este período.</div>
@else
    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width: 30%">Nombre del Producto</th>
                <th style="width: 25%">Proyecto Asociado</th>
                <th style="width: 15%" class="text-center">Estado Revisión</th>
                <th style="width: 15%" class="text-center">Soporte Evidencia</th>
                <th style="width: 15%" class="text-center">Fecha Carga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $prod)
                <tr>
                    <td><strong>{{ $prod->nombre }}</strong></td>
                    <td style="font-size: 10px;">{{ $prod->project?->nombre ?? '—' }}</td>
                    <td class="text-center">
                        @php
                            $estado = $prod->estado_revision instanceof \BackedEnum
                                ? $prod->estado_revision->value
                                : ((string) ($prod->estado_revision ?? 'pendiente'));
                            $clase = $estado === 'aprobado' ? 'badge-green' : ($estado === 'rechazado' ? 'badge-red' : 'badge-amber');
                        @endphp
                        <span class="badge {{ $clase }}">{{ ucfirst($estado) }}</span>
                    </td>
                    <td class="text-center" style="font-size: 10px;">
                        @if($prod->archivo && $prod->url_repositorio) Archivo + Repositorio
                        @elseif($prod->archivo) Archivo local
                        @elseif($prod->url_repositorio) Link externo
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td class="text-center" style="font-size: 10px;">{{ $prod->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
