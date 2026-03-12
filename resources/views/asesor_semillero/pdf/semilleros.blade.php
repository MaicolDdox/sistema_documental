@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte Completo de Semilleros')

@section('content')
    @if($semilleros->isEmpty())
        <p style="text-align: center; color: #666;">No hay semilleros registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap">
            <thead>
                <tr>
                    <th>Nombre del Semillero</th>
                    <th>Estado / Código</th>
                    <th>Grupo de Investigación</th>
                    <th>Líder</th>
                    <th>Proyectos Activos</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semilleros as $sem)
                    <tr>
                        <td style="font-weight: bold; width: 30%;">{{ $sem->nombre }}</td>
                        <td>
                            <strong>{{ ucfirst($sem->estado?->value ?? '—') }}</strong><br>
                            <span style="color: #666;">{{ $sem->codigo ?? 'Sin código' }}</span>
                        </td>
                        <td>{{ $sem->researchGroup?->nombre ?? '—' }}</td>
                        <td>
                            @if($sem->leader && $sem->leader->person)
                                {{ $sem->leader->person->primer_nombre }} {{ $sem->leader->person->primer_apellido }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $sem->projects->count() }}</td>
                        <td>{{ $sem->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
