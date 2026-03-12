@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte General del Semillero (Completo)')

@section('content')
    <h2 style="font-size: 14px; color: #0a1628; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 20px;">1. Listado de Proyectos</h2>
    @if($proyectos->isEmpty())
        <p style="text-align: center; color: #666;">No hay proyectos registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Líneas / Modalidad</th>
                    <th>Fechas</th>
                    <th>Área / Tipo</th>
                    <th>Macroproyecto</th>
                    <th>Autores</th>
                    <th>Productos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyectos as $proy)
                    <tr>
                        <td style="font-weight: bold; width: 25%;">{{ $proy->nombre }}</td>
                        <td>
                            <strong>Inv:</strong> {{ $proy->researchLine?->nombre ?? '—' }}<br>
                            <strong>P. Form:</strong> {{ $proy->projectModality?->nombre ?? '—' }}
                        </td>
                        <td>
                            <strong>In:</strong> {{ $proy->fecha_inicio ? \Carbon\Carbon::parse($proy->fecha_inicio)->format('d/m/Y') : '—' }}<br>
                            <strong>Fin:</strong> {{ $proy->fecha_fin ? \Carbon\Carbon::parse($proy->fecha_fin)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <strong>Área:</strong> {{ $proy->knowledgeNetwork?->nombre ?? '—' }}<br>
                            <strong>Tipo:</strong> {{ $proy->investigationType?->nombre ?? '—' }}
                        </td>
                        <td>{{ $proy->macroproject?->nombre ?? '—' }}</td>
                        <td>{{ $proy->projectAuthors->where('activo', true)->count() }} Act.</td>
                        <td>{{ $proy->products->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="page-break-after: always;"></div>

    <h2 style="font-size: 14px; color: #0a1628; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 20px;">2. Listado de Productos</h2>
    @if($productos->isEmpty())
        <p style="text-align: center; color: #666;">No hay productos registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Proyecto</th>
                    <th>Estado de Revisión</th>
                    <th>Enlace o Archivo</th>
                    <th>Autores Participantes</th>
                    <th>Fecha Carga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $prod)
                    <tr>
                        <td style="font-weight: bold;">{{ $prod->nombre }}</td>
                        <td>{{ $prod->project?->nombre ?? '—' }}</td>
                        <td>
                            @php
                                $estado = $prod->estado_revision ?? 'pendiente';
                                $clase = $estado == 'aprobado' ? 'badge-green' : ($estado == 'rechazado' ? 'badge-red' : 'badge-amber');
                            @endphp
                            <span class="badge {{ $clase }}">{{ ucfirst($estado) }}</span>
                        </td>
                        <td>
                            @if($prod->archivo) Local @endif
                            @if($prod->archivo && $prod->url_repositorio) | @endif
                            @if($prod->url_repositorio) Repositorio @endif
                            @if(!$prod->archivo && !$prod->url_repositorio) — @endif
                        </td>
                        <td>{{ $prod->productAuthors->count() }}</td>
                        <td>{{ $prod->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="page-break-after: always;"></div>

    <h2 style="font-size: 14px; color: #0a1628; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 20px;">3. Listado de Aprendices</h2>
    @if($aprendices->isEmpty())
        <p style="text-align: center; color: #666;">No hay aprendices vinculados en el rango seleccionado.</p>
    @else
        <table class="table-wrap" style="font-size: 10px;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Género</th>
                    <th>Contacto</th>
                    <th>EPS</th>
                    <th>Cargo / Vinculación</th>
                    <th>Programa de Formación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aprendices as $ap)
                    @php $p = $ap->person; @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $p?->primer_nombre }} {{ $p?->primer_apellido }}</td>
                        <td>{{ $ap->tipo_documento?->value }} {{ $ap->numero_documento }}</td>
                        <td>{{ $p?->genero ?? '—' }}</td>
                        <td>{{ $p?->email_institucional ?? '—' }}<br>C: {{ $p?->celular ?? '—' }} / T: {{ $p?->telefono ?? '—' }}</td>
                        <td>{{ $p?->eps ?? '—' }}</td>
                        <td>{{ $p?->entityPosition?->nombre ?? '—' }} <br> {{ $p?->linkageType?->nombre ?? '—' }}</td>
                        <td>{{ $p?->trainingProgram?->nombre ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
