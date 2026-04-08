@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte de Proyectos')
@section('subtitle', 'Detalle completo de proyectos del semillero')

@section('content')

@php
$financiacionLabels = [
    'capacidad_instalada' => 'Capacidad instalada',
    'financiado'          => 'Financiado',
    'con_alianza'         => 'Con alianza',
];
$totalActivos   = $proyectos->where('estado.value', 'activo')->count();
$totalInactivos = $proyectos->where('estado.value', 'inactivo')->count();
@endphp

{{-- Tarjetas de resumen --}}
<div class="metrics-row">
    <div class="metric-card metric-green">
        <span class="metric-label">Total Proyectos</span>
        <span class="metric-value">{{ $proyectos->count() }}</span>
        <span class="metric-sub">en el período</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Activos</span>
        <span class="metric-value" style="color:#39A900">{{ $totalActivos }}</span>
        <span class="metric-sub">en ejecución</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Inactivos</span>
        <span class="metric-value" style="color:#64748b">{{ $totalInactivos }}</span>
        <span class="metric-sub">finalizados/pausados</span>
    </div>
    <div class="metric-card metric-blue">
        <span class="metric-label">Total Autores</span>
        <span class="metric-value">{{ $proyectos->sum(fn($p) => $p->projectAuthors->where('activo', true)->count()) }}</span>
        <span class="metric-sub">activos en proyectos</span>
    </div>
    <div class="metric-card metric-amber">
        <span class="metric-label">Productos</span>
        <span class="metric-value">{{ $proyectos->sum(fn($p) => $p->products->count()) }}</span>
        <span class="metric-sub">generados</span>
    </div>
</div>

@if($proyectos->isEmpty())
    <p class="text-muted" style="text-align:center; padding:20px 0">No hay proyectos registrados en el período seleccionado.</p>
@else
    <div class="section-title">Detalle de Proyectos</div>
    <div class="section-subtitle">Ordenados por fecha de creación (más reciente primero)</div>

    <table class="table-wrap">
        <thead>
            <tr>
                <th style="width:20%">Proyecto</th>
                <th style="width:8%">Estado</th>
                <th>Semillero</th>
                <th>Línea de Inv. / Modalidad</th>
                <th>Tipo Inv. / Financiación</th>
                <th>Área Temática</th>
                <th>Fechas</th>
                <th style="text-align:center">Autores<br>Activos</th>
                <th style="text-align:center">Productos</th>
                <th>Macroproyecto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proyectos as $proy)
            @php
                $autoresActivos = $proy->projectAuthors->where('activo', true);
                $estadoVal = $proy->estado?->value ?? 'activo';
            @endphp
            <tr>
                <td>
                    <span class="text-bold">{{ $proy->nombre }}</span>
                    @if($proy->descripccion)
                    <br><span class="text-muted text-small">{{ Str::limit($proy->descripccion, 70) }}</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $estadoVal === 'activo' ? 'badge-green' : 'badge-slate' }}">
                        {{ ucfirst($estadoVal) }}
                    </span>
                </td>
                <td class="text-small">
                    @if($proy->seedlings->isNotEmpty())
                        {{ $proy->seedlings->first()->nombre }}
                    @else — @endif
                </td>
                <td class="text-small">
                    <strong>{{ $proy->researchLine?->nombre ?? '—' }}</strong><br>
                    <span class="text-muted">{{ $proy->projectModality?->nombre ?? '—' }}</span>
                </td>
                <td class="text-small">
                    <strong>{{ $proy->investigationType?->nombre ?? '—' }}</strong><br>
                    @if($proy->tipo_financiacion)
                        <span class="badge badge-teal">{{ $financiacionLabels[$proy->tipo_financiacion] ?? $proy->tipo_financiacion }}</span>
                    @else
                        <span class="text-muted">Sin financiación</span>
                    @endif
                </td>
                <td class="text-small text-muted">{{ $proy->thematicArea?->nombre ?? '—' }}</td>
                <td class="text-small">
                    <strong>Inicio:</strong> {{ $proy->fecha_inicio?->format('d/m/Y') ?? '—' }}<br>
                    <strong>Fin:</strong> <span class="{{ $proy->fecha_fin ? '' : 'text-green' }}">{{ $proy->fecha_fin?->format('d/m/Y') ?? 'En curso' }}</span>
                </td>
                <td style="text-align:center">
                    <span class="badge badge-purple">{{ $autoresActivos->count() }}</span>
                    <br>
                    @foreach($autoresActivos->take(3) as $autor)
                        <span class="text-small text-muted" style="display:block; font-size:8px">
                            {{ $autor->user?->person?->primer_nombre }} {{ $autor->user?->person?->primer_apellido }}
                        </span>
                    @endforeach
                    @if($autoresActivos->count() > 3)
                        <span style="font-size:8px; color:#94a3b8">+{{ $autoresActivos->count() - 3 }} más</span>
                    @endif
                </td>
                <td style="text-align:center">
                    <span class="badge badge-blue">{{ $proy->products->count() }}</span>
                </td>
                <td class="text-small text-muted">
                    {{ $proy->macroProject?->nombre ?? 'Ninguno' }}
                    @if($proy->macroProject)
                        <br><span class="badge badge-slate" style="font-size:8px">{{ $proy->macroProject->codigo }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
