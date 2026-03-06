@extends('layouts.sgd')

@section('title', 'Asesores')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Asesores</h1>
    <p class="text-sm text-slate-500 mt-0.5">Asesores internos y externos del semillero</p>
</div>

@if(!$semillero)
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder. No hay asesores que mostrar.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
<div class="mb-4">
    <h2 class="text-base font-semibold text-slate-900">Asesores del Semillero</h2>
    <p class="text-xs text-slate-500 mt-0.5">Asesores internos y externos vinculados a {{ $semillero->nombre }}</p>
</div>

<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Tipo</th>
                    <th class="text-left">Especialidad</th>
                    <th class="text-left">Cuenta en sistema</th>
                    <th class="text-left">Estado</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vinculos as $vinculo)
                @php
                    $a = $vinculo->externalAdvisor;
                    $tipo = $a && $a->user_id ? 'interno' : 'externo';
                    $tiene_cuenta = $a && (bool) $a->user_id;
                    $activo = $vinculo->activo ?? true;
                @endphp
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $a->nombre_completo ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($tipo === 'interno')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Interno</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Externo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $a->institucion ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($tiene_cuenta)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✓ Activo</span>
                        @else
                        <span class="text-slate-500 text-sm">Sin cuenta</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($activo)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <a href="#" class="sgd-btn-secondary inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border border-slate-200">Ver</a>
                            @can('asesores_externos.vincular_semillero')
                            <form action="{{ route('lider-sem.asesores.toggle', $vinculo) }}" method="POST" class="inline" onsubmit="return confirm('¿{{ $activo ? 'Desactivar' : 'Activar' }} este asesor en el semillero?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $activo ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }} transition-colors">
                                    {{ $activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                        No hay asesores vinculados a este semillero.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection
