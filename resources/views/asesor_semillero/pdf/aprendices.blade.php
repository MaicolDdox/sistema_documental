@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte de Aprendices')
@section('subtitle', 'Listado de aprendices vinculados al semillero')

@section('content')

{{-- Resumen --}}
<div class="metrics-row">
    <div class="metric-card metric-blue">
        <span class="metric-label">Total Aprendices</span>
        <span class="metric-value">{{ $aprendices->count() }}</span>
        <span class="metric-sub">en el período</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Con EPS</span>
        <span class="metric-value">{{ $aprendices->filter(fn($a) => $a->person?->eps)->count() }}</span>
        <span class="metric-sub">registrada</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Con Correo Inst.</span>
        <span class="metric-value">{{ $aprendices->filter(fn($a) => $a->person?->email_institucional)->count() }}</span>
        <span class="metric-sub">registrado</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Programa + Común</span>
        @php
            $programaComun = $aprendices
                ->filter(fn($a) => $a->person?->trainingProgram)
                ->groupBy(fn($a) => $a->person->trainingProgram->nombre)
                ->sortByDesc(fn($g) => $g->count())
                ->keys()->first() ?? '—';
        @endphp
        <span class="metric-value" style="font-size:11px; line-height:1.2; color:#2563eb">{{ \Illuminate\Support\Str::limit($programaComun, 20) }}</span>
        <span class="metric-sub">más frecuente</span>
    </div>
</div>

@if($aprendices->isEmpty())
    <p class="text-muted" style="text-align:center; padding:20px 0">No hay aprendices registrados en el período seleccionado.</p>
@else
    <div class="section-title">Detalle de Aprendices</div>
    <div class="section-subtitle">Ordenados por fecha de registro (más reciente primero).</div>

    <table class="table-wrap" style="font-size:9.5px">
        <thead>
            <tr>
                <th style="width:20%">Nombre Completo</th>
                <th>Documento</th>
                <th>Género</th>
                <th>Correo / Celular</th>
                <th>EPS</th>
                <th>Cargo / Vinculación</th>
                <th>Programa de Formación</th>
                <th>Semillero</th>
                <th>Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aprendices as $ap)
            @php $p = $ap->person; @endphp
            <tr>
                <td>
                    <span class="text-bold">
                        {{ $p?->primer_nombre }} {{ $p?->segundo_nombre }}<br>
                        {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-slate">{{ $ap->tipo_documento?->value }}</span><br>
                    {{ $ap->numero_documento }}
                </td>
                <td>{{ $p?->genero ?? '—' }}</td>
                <td>
                    {{ $p?->email_institucional ?? '—' }}<br>
                    <span class="text-muted">{{ $p?->celular ?? '—' }}</span>
                </td>
                <td>{{ $p?->eps ?? '—' }}</td>
                <td>
                    {{ $p?->entityPosition?->nombre ?? '—' }}<br>
                    <span class="text-muted">{{ $p?->linkageType?->nombre ?? '—' }}</span>
                </td>
                <td>{{ $p?->trainingProgram?->nombre ?? '—' }}</td>
                <td>
                    @if($ap->seedlings->isNotEmpty())
                        @foreach($ap->seedlings as $sem)
                            <span class="pill pill-green">{{ \Illuminate\Support\Str::limit($sem->nombre, 20) }}</span>
                        @endforeach
                    @else —
                    @endif
                </td>
                <td class="text-muted">{{ $ap->created_at?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
