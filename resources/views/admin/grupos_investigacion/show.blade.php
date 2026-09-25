@extends('layouts.sgd')

@section('title', $grupo->nombre)
@section('header', '')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('admin.grupos-investigacion.index') }}" class="hover:text-[#39A900]">Grupos de Investigación</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">{{ $grupo->nombre }}</span>
</nav>

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $grupo->nombre }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Código {{ $grupo->codigo }}</p>
    </div>
    @can('grupos_investigacion.editar')
    <a href="{{ route('admin.grupos-investigacion.edit', $grupo) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 text-slate-700 hover:bg-slate-50">Editar</a>
    @endcan
</div>

<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Detalle</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Director asignado</p>
                <p class="text-sm text-slate-700">{{ $grupo->director?->person?->nombre_completo ?? $grupo->director?->email ?? 'Sin director asignado' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Líneas de investigación</p>
                <p class="text-sm text-slate-700">{{ $grupo->lineasInvestigacion->pluck('nombre')->join(', ') ?: '—' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Descripción</p>
                <p class="text-sm text-slate-700">{{ $grupo->descripcion ?? 'Aún no completada por el director asignado.' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Productos Minciencias del grupo ({{ $grupo->mincienciasProducts->count() }})</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($grupo->mincienciasProducts as $producto)
            <li class="px-5 py-3 flex items-center justify-between text-sm gap-3">
                <span class="font-medium text-slate-800">{{ $producto->nombre }}</span>
                <a href="{{ route('admin.minciencias.show', $producto) }}" class="text-[#39A900] hover:underline text-xs font-medium">Ver</a>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Aún no hay productos registrados en este grupo.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
