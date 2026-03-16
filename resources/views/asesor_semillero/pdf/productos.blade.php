@extends('asesor_semillero.pdf.layout')

@section('title', 'Reporte Completo de Productos')

@section('content')
    @if($productos->isEmpty())
        <p style="text-align: center; color: #666;">No hay productos registrados en el rango seleccionado.</p>
    @else
        <table class="table-wrap">
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Proyecto Asociado</th>
                    <th>Estado</th>
                    <th>Disponibilidad</th>
                    <th>Autores Participantes</th>
                    <th>Fecha de Carga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $prod)
                    <tr>
                        <td style="font-weight: bold; width: 35%;">{{ $prod->nombre }}</td>
                        <td>{{ $prod->project?->nombre ?? 'Sin proyecto' }}</td>
                        <td>
                            @php
                                $estado = $prod->estado_revision ?? 'pendiente';
                                $clase = $estado == 'aprobado' ? 'badge-green' : ($estado == 'rechazado' ? 'badge-red' : 'badge-amber');
                            @endphp
                            <span class="badge {{ $clase }}">{{ ucfirst($estado) }}</span>
                        </td>
                        <td>
                            @if($prod->archivo) Archivo local @endif
                            @if($prod->archivo && $prod->url_repositorio) <br> @endif
                            @if($prod->url_repositorio) <a href="{{ $prod->url_repositorio }}" style="color: #1e40af; text-decoration: none;">Link Repositorio</a> @endif
                            @if(!$prod->archivo && !$prod->url_repositorio) No disponible @endif
                        </td>
                        <td>{{ $prod->productAuthors->count() }} autores</td>
                        <td>{{ $prod->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
