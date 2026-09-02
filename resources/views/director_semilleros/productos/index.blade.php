@extends('layouts.sgd')

@section('title', 'Productos')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Productos — aprobación definitiva</h1>
    <p class="text-sm text-slate-500 mt-0.5">Productos finales ya aprobados por el Líder de Semillero, pendientes de tu visto bueno definitivo.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Producto</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Proyecto</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Semillero</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Líder de Proyecto</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Estado definitivo</th>
                    <th class="text-right px-4 py-3 font-medium text-slate-500">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evidencias as $ev)
                @php
                    $estado = $ev->estado_revision_director?->value ?? $ev->estado_revision_director;
                    $lider = $ev->project?->liderProyecto;
                @endphp
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                        <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                        @if($ev->archivo)
                        <a href="{{ route('dir-sem.productos.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium mt-1 inline-block">Descargar</a>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $ev->project?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $ev->project?->seedling?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $lider?->person?->nombre_completo ?? $lider?->email ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($estado === 'aprobado')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado definitivo</span>
                        @elseif($estado === 'rechazado')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($estado === 'pendiente' || $estado === null)
                        <div class="flex items-center justify-end gap-3">
                            <form action="{{ route('dir-sem.productos.aprobar', $ev) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Aprobar definitivo</button>
                            </form>
                            <button type="button" onclick="document.getElementById('rechazo-dir-{{ $ev->id }}').classList.toggle('hidden')"
                                    class="text-xs font-medium text-red-600 hover:text-red-800">Rechazar</button>
                        </div>
                        <form id="rechazo-dir-{{ $ev->id }}" action="{{ route('dir-sem.productos.rechazar', $ev) }}" method="POST" class="hidden mt-2 text-left">
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
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No hay productos pendientes de aprobación definitiva.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
