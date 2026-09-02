@extends('layouts.sgd')

@section('title', 'Producto Minciencias')
@section('header', '')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Producto Minciencias</h1>
        <p class="text-sm text-slate-500 mt-0.5">Productos Minciencias personales — solo visibles para ti.</p>
    </div>
    <a href="{{ route('co-investigador.productos.create') }}"
       class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo producto
    </a>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
    {{ session('success') }}
</div>
@endif

<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Línea de investigación</th>
                    <th class="text-left">Fechas</th>
                    <th class="text-left">Archivos</th>
                    <th class="text-left">Estado</th>
                    <th class="text-left">Revisión</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                @php
                    $estadoVal = $producto->estado->value ?? $producto->estado;
                @endphp
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $producto->nombre }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $producto->researchLine?->nombre ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600 text-xs">
                        {{ $producto->fecha_inicio?->format('d/m/Y') ?? '—' }} - {{ $producto->fecha_fin?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $producto->files_count ?? $producto->files()->count() }}</td>
                    <td class="px-5 py-3">
                        @if($estadoVal === 'activo')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @php $revVal = $producto->estado_revision?->value ?? 'pendiente'; @endphp
                        @if($revVal === 'aprobado')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                        @elseif($revVal === 'rechazado')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('co-investigador.productos.show', $producto) }}"
                               class="text-xs font-medium text-slate-600 hover:text-slate-900">Ver</a>
                            <a href="{{ route('co-investigador.productos.edit', $producto) }}"
                               class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Editar</a>
                            <form action="{{ route('co-investigador.productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto Minciencias? Esta acción no se puede deshacer.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                        Aún no has registrado ningún producto Minciencias.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
