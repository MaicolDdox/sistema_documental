@extends('layouts.sgd')

@section('title', 'Dashboard')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">Resumen de tus productos Minciencias.</p>
</div>

@if($grupo)
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h2 class="text-sm font-semibold text-slate-900">Grupo de Investigación</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Nombre</p>
            <p class="text-sm text-slate-700">{{ $grupo->nombre }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Código</p>
            <p class="text-sm text-slate-700">{{ $grupo->codigo }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Director</p>
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
@else
<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 mb-6">
    No estás vinculado a ningún grupo de investigación. Contacta a tu director de grupo de investigación o al administrador del sistema.
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-1">Pendientes</p>
        <p class="text-2xl font-bold text-slate-900">{{ $conteos['pendiente'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Aprobados</p>
        <p class="text-2xl font-bold text-slate-900">{{ $conteos['aprobado'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Rechazados</p>
        <p class="text-2xl font-bold text-slate-900">{{ $conteos['rechazado'] }}</p>
    </div>
</div>

<a href="{{ route('co-investigador-gdi.productos.index') }}" class="inline-flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
    Ver mis productos
</a>
@endsection
