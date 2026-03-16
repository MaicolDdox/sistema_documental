@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte Completo de Proyectos')

@section('content')
    @if($proyectos->isEmpty())
        <p style="text-align: center; color: #666;">No hay proyectos registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap">
            <thead>
                <tr>
                    <th>Nombre y Macroproyecto</th>
                    <th>Líneas / Modalidad</th>
                    <th>Fechas</th>
                    <th>Área / Tipo Inv.</th>
                    <th>Autores Activos</th>
                    <th>Total Prod.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyectos as $proy)
                    <tr>
                        <td style="font-weight: bold; width: 30%;">
                            {{ $proy->nombre }}<br>
                            <span style="font-weight: normal; color: #666; font-size: 10px;">
                                Macro: {{ $proy->macroproject?->nombre ?? 'Ninguno' }}
                            </span>
                        </td>
                        <td>
                            <strong>Inv:</strong> {{ $proy->researchLine?->nombre ?? '—' }} <br>
                            <strong>Mod:</strong> {{ $proy->projectModality?->nombre ?? '—' }}
                        </td>
                        <td>
                            <strong>In:</strong> {{ $proy->fecha_inicio ? \Carbon\Carbon::parse($proy->fecha_inicio)->format('d/m/Y') : '—' }}<br>
                            <strong>Fin:</strong> {{ $proy->fecha_fin ? \Carbon\Carbon::parse($proy->fecha_fin)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <strong>Área:</strong> {{ $proy->knowledgeNetwork?->nombre ?? '—' }}<br>
                            <strong>Tipo:</strong> {{ $proy->investigationType?->nombre ?? '—' }}
                        </td>
                        <td>{{ $proy->projectAuthors->where('activo', true)->count() }}</td>
                        <td>{{ $proy->products->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
