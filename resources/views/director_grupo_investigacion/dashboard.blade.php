@extends('layouts.sgd')

@section('title', 'Dashboard')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ $grupo->nombre }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Panel del Director de Grupo de Investigación — código {{ $grupo->codigo }}</p>
</div>

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

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <a href="{{ route('director-grupo-investigacion.minciencias.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-[#39A900] transition-colors">
        <h2 class="text-sm font-semibold text-slate-900 mb-1">Productos Minciencias</h2>
        <p class="text-sm text-slate-500">Revisa y aprueba los productos de tus co-investigadores.</p>
    </a>
    <a href="{{ route('director-grupo-investigacion.grupo.edit') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-[#39A900] transition-colors">
        <h2 class="text-sm font-semibold text-slate-900 mb-1">Información del grupo</h2>
        <p class="text-sm text-slate-500">Completa la descripción, el logo y la línea de investigación.</p>
    </a>
</div>
@endsection
