@extends('layouts.sgd')

@section('title', 'Proyectos')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Proyectos</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        @if($semillero)
            Proyectos vinculados al semillero {{ $semillero->nombre }}
        @else
            Proyectos del semillero
        @endif
    </p>
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
<div x-data="{ showDetailId: null }">
    <div class="mb-4">
        <h2 class="text-base font-semibold text-slate-900">Proyectos del Semillero</h2>
        <p class="text-xs text-slate-500 mt-0.5">Proyectos vinculados a {{ $semillero->nombre }} — Solo los creados por asesores del semillero</p>
    </div>

    <div class="sgd-table-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Título</th>
                        <th class="text-left">Asesor</th>
                        <th class="text-left">Integrantes</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Avance</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proyectos as $proyecto)
                    @php
                        $creador = $proyecto->projectCreator;
                        $asesorNombre = $creador?->person?->nombre_completo ?? $creador?->email ?? '—';
                        $estadoVal = $proyecto->estado->value ?? $proyecto->estado;
                        $avance = $proyecto->avance ?? 0;
                    @endphp
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $proyecto->nombre ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $asesorNombre }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $proyecto->integrantes_count ?? 0 }}</td>
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
                                <span class="text-xs font-medium text-slate-600 w-9">{{ $avance }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <button type="button"
                                    @click="showDetailId = {{ $proyecto->id }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 hover:text-slate-900 transition"
                                    title="Ver detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s2.25-6.75 9.75-6.75S21.75 12 21.75 12 19.5 18.75 12 18.75 2.25 12 2.25 12z" />
                                    <circle cx="12" cy="12" r="3.25" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                            No hay proyectos vinculados a este semillero.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal detalle proyecto --}}
    <div x-show="showDetailId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-slate-200"
             @click.self="showDetailId = null">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Detalle del proyecto</h2>
                <button type="button" @click="showDetailId = null" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                @foreach($proyectos as $proyecto)
                @php
                    $creador = $proyecto->projectCreator;
                    $asesorNombre = $creador?->person?->nombre_completo ?? $creador?->email ?? '—';
                    $estadoVal = $proyecto->estado->value ?? $proyecto->estado;
                    $avance = $proyecto->avance ?? 0;
                @endphp
                <div x-show="showDetailId === {{ $proyecto->id }}" x-cloak class="space-y-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 mb-1">{{ $proyecto->nombre ?? 'Sin título' }}</h3>
                        <p class="text-xs text-slate-500">Asesor: {{ $asesorNombre }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Integrantes</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->integrantes_count ?? 0 }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Estado</p>
                            <p class="text-sm font-medium text-slate-700">{{ $estadoVal === 'activo' ? 'Activo' : 'Inactivo' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 sm:col-span-2">
                            <p class="text-xs text-slate-400 mb-0.5">Avance</p>
                            <div class="flex items-center gap-2 min-w-[100px]">
                                <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-[#39A900] transition-all duration-300" style="width: {{ $avance }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-600 w-9">{{ $avance }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
@endsection
