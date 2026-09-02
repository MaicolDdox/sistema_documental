@extends('layouts.sgd')

@section('title', 'Productos')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Productos — {{ $semillero->nombre ?? 'Semillero' }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Evidencias de Formulación, Ejecución y Producto Final subidas por los Líderes de Proyecto, agrupadas por proyecto. Formulación y Ejecución quedan resueltas con tu aprobación; Producto Final pasa al Director de Semilleros para el visto bueno definitivo.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

@php
    $tipoLabels = [
        'formulacion' => 'Formulación',
        'ejecucion' => 'Ejecución',
        'producto_final' => 'Producto final',
    ];
@endphp

<div class="space-y-4">
    @forelse($proyectosConEvidencias as $grupo)
    @php
        $proyecto = $grupo->proyecto;
        $lider = $proyecto?->liderProyecto;
    @endphp
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ open: {{ $grupo->pendientes_count > 0 ? 'true' : 'false' }} }">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-3.5 bg-slate-50 border-b border-slate-100 text-left">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $proyecto?->nombre ?? 'Proyecto sin nombre' }}</p>
                    <p class="text-xs text-slate-500">Líder de Proyecto: {{ $lider?->person?->nombre_completo ?? $lider?->email ?? '—' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if($grupo->pendientes_count > 0)
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ $grupo->pendientes_count }} pendiente{{ $grupo->pendientes_count > 1 ? 's' : '' }}</span>
                @endif
                <span class="text-xs text-slate-400">{{ $grupo->evidencias->count() }} producto{{ $grupo->evidencias->count() > 1 ? 's' : '' }}</span>
            </div>
        </button>

        <div x-show="open" x-transition>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="text-left px-4 py-3 font-medium text-slate-500">Producto</th>
                            <th class="text-left px-4 py-3 font-medium text-slate-500">Tipo</th>
                            <th class="text-left px-4 py-3 font-medium text-slate-500">Estado</th>
                            <th class="text-right px-4 py-3 font-medium text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupo->evidencias as $ev)
                        @php
                            $estado = $ev->estado_revision_lider?->value ?? $ev->estado_revision_lider;
                            $tipoValue = $ev->tipo?->value ?? $ev->tipo;
                        @endphp
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                                @if($ev->archivo)
                                <a href="{{ route('lider-sem.productos.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium mt-1 inline-block">Descargar</a>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ $tipoLabels[$tipoValue] ?? $tipoValue }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($estado === 'aprobado')
                                    @if($tipoValue === 'producto_final')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado — enviado al Director</span>
                                    @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                                    @endif
                                @elseif($estado === 'rechazado')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($estado === 'pendiente' || $estado === null)
                                <div class="flex items-center justify-end gap-3">
                                    <form action="{{ route('lider-sem.productos.aprobar', $ev) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Aprobar</button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('rechazo-{{ $ev->id }}').classList.toggle('hidden')"
                                            class="text-xs font-medium text-red-600 hover:text-red-800">Rechazar</button>
                                </div>
                                <form id="rechazo-{{ $ev->id }}" action="{{ route('lider-sem.productos.rechazar', $ev) }}" method="POST" class="hidden mt-2 text-left">
                                    @csrf @method('PATCH')
                                    <textarea name="observaciones" rows="2" required placeholder="Motivo del rechazo (obligatorio)"
                                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs mb-2"></textarea>
                                    <button type="submit" class="text-xs font-medium bg-red-600 text-white px-3 py-1.5 rounded-lg">Confirmar rechazo</button>
                                </form>
                                @else
                                <span class="text-xs text-slate-400">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-10 text-center text-slate-500 text-sm">No hay proyectos con evidencias pendientes de revisión todavía.</div>
    @endforelse
</div>
@endsection
