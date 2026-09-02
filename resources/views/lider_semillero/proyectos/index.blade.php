@extends('layouts.sgd')

@section('title', 'Proyectos')
@section('header', '')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Proyectos</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            @if($semillero)
                Proyectos del semillero {{ $semillero->nombre }}
            @else
                Proyectos del semillero
            @endif
        </p>
    </div>
    @if($semillero)
    <a href="{{ route('lider-sem.proyectos.create') }}"
       class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo proyecto
    </a>
    @endif
</div>

@if(!$semillero)
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder. No hay proyectos que mostrar.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
<div>
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
                        <th class="text-left">Líder de Proyecto</th>
                        <th class="text-left">Fechas</th>
                        <th class="text-left">Integrantes</th>
                        <th class="text-left">Aprendices</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Avance</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proyectos as $proyecto)
                    @php
                        $lider = $proyecto->liderProyecto;
                        $liderNombre = $lider?->person?->nombre_completo ?? $lider?->email ?? 'Sin asignar';
                        $estadoVal = $proyecto->estado->value ?? $proyecto->estado;
                        $avance = $proyecto->avance ?? 0;
                        $avanceLabel = $proyecto->avance_label ?? 'Sin iniciar';
                    @endphp
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $proyecto->nombre ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $liderNombre }}</td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            {{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }} - {{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $proyecto->integrantes_count ?? 0 }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $proyecto->aprendices_count ?? 0 }}</td>
                        <td class="px-5 py-3">
                            @if($estadoVal === 'activo')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 min-w-[100px]">
                                <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-[#39A900] transition-all duration-300" style="width: {{ $avance }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-600 w-10">{{ $avance }}%</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">{{ $avanceLabel }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('lider-sem.proyectos.edit', $proyecto) }}"
                               class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                            No hay proyectos vinculados a este semillero.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
