@extends('layouts.sgd')

@section('title', 'Panel del Líder de Semillero')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Panel del Líder de Semillero</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        Resumen del semillero {{ $metricas['nombre_semillero'] }}.
    </p>
</div>

{{-- 5 tarjetas de métricas --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Semillero a cargo</p>
        <p class="text-2xl font-bold text-slate-900">{{ $metricas['semilleros_a_cargo'] }}</p>
        <p class="text-xs mt-1">
            @if($metricas['estado_semillero'] === 'activo')
            <span class="text-green-600 font-medium">{{ $metricas['nombre_semillero'] }} activo</span>
            @else
            <span class="text-slate-500">{{ $metricas['nombre_semillero'] }}</span>
            @endif
        </p>
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Integrantes</p>
        <p class="text-2xl font-bold text-slate-900">{{ $metricas['integrantes'] }}</p>
        @if($metricas['integrantes_nuevos_texto'])
        <p class="text-xs text-slate-500 mt-1">{{ $metricas['integrantes_nuevos_texto'] }}</p>
        @endif
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Productos pendientes</p>
        <p class="text-2xl font-bold text-slate-900">{{ $metricas['productos_pendientes'] }}</p>
        @if($metricas['productos_pendientes_texto'])
        <p class="text-xs text-amber-600 mt-1">{{ $metricas['productos_pendientes_texto'] }}</p>
        @endif
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Proyectos activos</p>
        <p class="text-2xl font-bold text-slate-900">{{ $metricas['proyectos_activos'] }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $metricas['proyectos_activos_texto'] }}</p>
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Sin proyecto activo</p>
        <p class="text-2xl font-bold text-slate-900">{{ $metricas['sin_proyecto_activo'] }}</p>
        @if($metricas['sin_proyecto_texto'])
        <p class="text-xs text-amber-600 mt-1">{{ $metricas['sin_proyecto_texto'] }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Proyectos del semillero --}}
        <div class="sgd-table-card bg-white overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Proyectos del semillero</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Proyectos vinculados a {{ $metricas['nombre_semillero'] }}</p>
                </div>
                <a href="{{ route('lider-sem.proyectos') }}" class="text-sm font-medium text-[#39A900] hover:underline">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="sgd-table text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Proyecto</th>
                            <th class="text-left">Estado</th>
                            <th class="text-left">Fechas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($proyectosDelSemillero ?? []) as $p)
                        @php
                            $estado = $p->estado_tablero ?? ($p->estado?->value ?? (string) ($p->estado ?? ''));
                            $estadoLabel = $estado === 'activo'
                                ? 'Activo'
                                : ($estado === 'inactivo'
                                    ? 'Inactivo'
                                    : ($estado === 'finalizado' ? 'Finalizado' : ($estado ?: '—')));
                        @endphp
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $p->nombre }}</td>
                            <td class="px-5 py-3">
                                @if($estado === 'activo')
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                @elseif($estado === 'finalizado')
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Finalizado</span>
                                @elseif($estado === 'inactivo')
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Inactivo</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">{{ $estadoLabel }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $p->fecha_inicio?->format('Y-m-d') ?? '—' }} → {{ $p->fecha_fin?->format('Y-m-d') ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">No hay proyectos vinculados a este semillero.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Productos pendientes de revisión --}}
        <div class="sgd-table-card bg-white overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Productos pendientes de revisión</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Últimas solicitudes de aprobación</p>
                </div>
                <a href="{{ route('lider-sem.productos') }}" class="text-sm font-medium text-[#39A900] hover:underline">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="sgd-table text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Producto</th>
                            <th class="text-left">Proyecto</th>
                            <th class="text-left">Líder de Proyecto</th>
                            <th class="text-left">Estado</th>
                            <th class="text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosPendientes as $ev)
                        @php
                            $lider = $ev->project?->liderProyecto;
                            $iniciales = $lider ? $lider->initials() : '—';
                            $nombreLider = $lider?->person?->nombre_completo ?? $lider?->email ?? '—';
                        @endphp
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $ev->nombre ?? 'Sin título' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $ev->project?->nombre ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center text-xs font-bold text-[#39A900]">{{ $iniciales }}</span>
                                    <span class="text-slate-700">{{ $nombreLider }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">pendiente</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <form method="POST"
                                          action="{{ route('lider-sem.productos.aprobar', $ev) }}"
                                          class="inline"
                                          onsubmit="return confirm('¿Aprobar este producto?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors">✓ Aprobar</button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('lider-sem.productos.rechazar', $ev) }}"
                                          class="inline"
                                          onsubmit="return confirm('¿Rechazar este producto? Se usará una observación automática.');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="observaciones" value="Rechazado desde el panel del líder de semillero.">
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition-colors">✕ Rechazar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay productos pendientes de revisión.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Integrantes sin proyecto activo --}}
        <div class="sgd-table-card bg-white overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-base font-semibold text-slate-900">Integrantes sin proyecto activo</h2>
                <p class="text-xs text-slate-500 mt-0.5">Aprendices sin proyecto activo</p>
            </div>
            <div class="overflow-x-auto">
                <table class="sgd-table text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Aprendiz</th>
                            <th class="text-left">Documento</th>
                            <th class="text-left">Semillero</th>
                            <th class="text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($integrantesSinProyecto as $integrante)
                        @php
                            $p = $integrante->person;
                            $nombre = $p?->nombre_completo ?? $integrante->email ?? '—';
                            $doc = $integrante->numero_documento ?? '—';
                            $iniciales = $integrante->initials();
                        @endphp
                        <tr>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">{{ $iniciales }}</span>
                                    <span class="font-medium text-slate-800">{{ $nombre }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $doc }}</td>
                            <td class="px-5 py-3">
                                @if($miSemillero)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ $miSemillero->nombre }}</span>
                                @else — @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($integrante->tiene_proyecto_activo ?? false)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Con proyecto activo</span>
                                @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Sin proyecto activo</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No hay integrantes sin proyecto activo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Columna derecha: Mi Semillero --}}
    <div class="space-y-6">
        @if($miSemillero)
        <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 text-center border-b border-slate-100">
                <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3 overflow-hidden">
                    <svg class="w-8 h-8 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <h3 class="font-semibold text-slate-900">Semillero {{ $miSemillero->nombre }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Código: {{ $miSemillero->codigo ?? '—' }}</p>
            </div>
            <div class="p-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Estado</span>
                    <span class="inline-flex items-center gap-1.5 text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ $miSemillero->estado->value === 'activo' ? 'Activo' : 'Inactivo' }}</span>
                </div>
            </div>
            <div class="p-4 pt-0">
                <a href="{{ route('lider-sem.info-semillero') }}" class="sgd-btn-secondary block w-full text-center px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">Ver información</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
