@extends('layouts.sgd')

@section('title', 'Dashboard')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">Resumen de tus productos Minciencias.</p>
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

<a href="{{ route('co-investigador-gdi.productos.index') }}" class="inline-flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
    Ver mis productos
</a>
@endsection
