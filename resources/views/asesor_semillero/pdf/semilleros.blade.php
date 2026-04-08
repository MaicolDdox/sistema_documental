@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte de Semilleros')
@section('subtitle', 'Información detallada de los semilleros asesorados')

@section('content')

{{-- Resumen --}}
<div class="metrics-row">
    <div class="metric-card metric-green">
        <span class="metric-label">Total Semilleros</span>
        <span class="metric-value">{{ $semilleros->count() }}</span>
        <span class="metric-sub">asesorados</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Total Miembros</span>
        <span class="metric-value">{{ $semilleros->sum('totalMiembros') }}</span>
        <span class="metric-sub">aprendices activos</span>
    </div>
    <div class="metric-card metric-green">
        <span class="metric-label">Proyectos Activos</span>
        <span class="metric-value">{{ $semilleros->sum('proyectosActivos') }}</span>
        <span class="metric-sub">en ejecución</span>
    </div>
    <div class="metric-card metric-amber">
        <span class="metric-label">Proyectos Inactivos</span>
        <span class="metric-value">{{ $semilleros->sum('proyectosInactivos') }}</span>
        <span class="metric-sub">finalizados</span>
    </div>
</div>

@if($semilleros->isEmpty())
    <p class="text-muted" style="text-align:center; padding:20px 0">No hay semilleros registrados.</p>
@else

{{-- Tabla principal de semilleros --}}
<div class="section-title">Detalle por Semillero</div>

<table class="table-wrap" style="margin-bottom: 18px;">
    <thead>
        <tr>
            <th style="width:22%">Nombre</th>
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
            <td class="text-bold">{{ $sem->nombre }}</td>
            <td>
                <span class="badge {{ $sem->estado?->value === 'activo' ? 'badge-green' : 'badge-slate' }}">
                    {{ ucfirst($sem->estado?->value ?? '—') }}
                </span><br>
                <span class="text-muted text-small">{{ $sem->codigo ?? 'Sin código' }}</span>
            </td>
            <td>{{ $sem->researchGroup?->nombre ?? '—' }}</td>
            <td>
                @if($sem->leader)
                    <strong>{{ $sem->leader->person ? trim($sem->leader->person->primer_nombre . ' ' . $sem->leader->person->primer_apellido) : $sem->leader->email }}</strong>
                @else
                    <span class="text-muted">Sin líder asignado</span>
                @endif
            </td>
            <td style="text-align:center"><span class="badge badge-blue">{{ $sem->totalMiembros }}</span></td>
            <td style="text-align:center"><span class="badge badge-green">{{ $sem->proyectosActivos }}</span></td>
            <td style="text-align:center"><span class="badge badge-slate">{{ $sem->proyectosInactivos }}</span></td>
            <td class="text-small text-muted">{{ $sem->created_at->format('d/m/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Detalle de miembros por semillero --}}
@foreach($semilleros as $sem)
    @if($sem->miembros->isNotEmpty())
    <div class="section" style="margin-top: 14px;">
        <div style="background:#f8fafc; border-left: 3px solid #39A900; padding: 6px 10px; margin-bottom: 8px; border-radius: 0 4px 4px 0;">
            <strong style="font-size:11px; color:#0a1628;">{{ $sem->nombre }}</strong>
            <span class="text-muted text-small"> — Listado de Miembros ({{ $sem->miembros->count() }})</span>
        </div>
        <table class="table-wrap" style="font-size:9.5px">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Correo</th>
                    <th>Programa de Formación</th>
                    <th>Vinculación</th>
                    <th>Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sem->miembros as $miembro)
                @php $mp = $miembro->person; @endphp
                <tr>
                    <td class="text-bold">
                        {{ $mp?->primer_nombre }} {{ $mp?->segundo_nombre }}
                        {{ $mp?->primer_apellido }} {{ $mp?->segundo_apellido }}
                    </td>
                    <td>{{ $miembro->tipo_documento?->value }} {{ $miembro->numero_documento }}</td>
                    <td class="text-muted">{{ $mp?->email_institucional ?? '—' }}</td>
                    <td>{{ $mp?->trainingProgram?->nombre ?? '—' }}</td>
                    <td>{{ $mp?->linkageType?->nombre ?? '—' }}</td>
                    <td class="text-muted">{{ $miembro->created_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endforeach

@endif

@endsection
