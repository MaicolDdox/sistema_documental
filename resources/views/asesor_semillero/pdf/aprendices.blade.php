@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte Completo de Aprendices')

@section('content')
    @if($aprendices->isEmpty())
        <p style="text-align: center; color: #666;">No hay aprendices registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap" style="font-size: 10px;">
            <thead>
                <tr>
                    <th>Nombre y Apellidos</th>
                    <th>Documento</th>
                    <th>Género</th>
                    <th>Contacto (Correo/Tel)</th>
                    <th>EPS</th>
                    <th>Cargo / Vinculación</th>
                    <th>Programa de Formación</th>
                    <th>Fecha Reg.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aprendices as $ap)
                    @php $p = $ap->person; @endphp
                    <tr>
                        <td><strong>{{ $p?->primer_nombre }} {{ $p?->segundo_nombre }} {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}</strong></td>
                        <td>{{ $ap->tipo_documento?->value }} {{ $ap->numero_documento }}</td>
                        <td>{{ $p?->genero ?? '—' }}</td>
                        <td>{{ $p?->email_institucional ?? '—' }}<br>{{ $p?->celular ?? '—' }} / {{ $p?->telefono ?? '—' }}</td>
                        <td>{{ $p?->eps ?? '—' }}</td>
                        <td>{{ $p?->entityPosition?->nombre ?? '—' }} <br> <span style="color:#666">{{ $p?->linkageType?->nombre ?? '—' }}</span></td>
                        <td>{{ $p?->trainingProgram?->nombre ?? '—' }}</td>
                        <td>{{ $ap->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
