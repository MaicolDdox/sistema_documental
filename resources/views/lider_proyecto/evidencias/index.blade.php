@extends('layouts.sgd')

@section('title', 'Evidencias')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Evidencias — {{ $proyecto->nombre }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">La evidencia de investigación y/o desarrollo no requiere aprobación. Formulación y Ejecución requieren revisión del Líder de Semillero. Producto final dispara la revisión de dos etapas.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <h2 class="text-base font-semibold text-slate-900 mb-4">Subir evidencia</h2>
    <form action="{{ route('lider-proyecto.evidencias.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo <span class="text-red-500">*</span></label>
            <select name="tipo" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                <option value="desarrollo">Evidencia de investigación y/o desarrollo (sin aprobación)</option>
                <option value="formulacion">Formulación (30% del avance)</option>
                <option value="ejecucion">Ejecución (50% del avance)</option>
                <option value="producto_final">Producto final (20% del avance — inicia revisión de 2 etapas)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
            <input type="text" name="nombre" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
            <textarea name="descripcion" rows="2" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo <span class="text-red-500">*</span></label>
            <input type="file" name="archivo" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm">Subir evidencia</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Evidencia de investigación y/o desarrollo</h3>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($desarrollo as $ev)
            <li class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if($ev->archivo)
                    <a href="{{ route('lider-proyecto.evidencias.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    <form action="{{ route('lider-proyecto.evidencias.destroy', $ev) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin evidencias de investigación y/o desarrollo todavía.</li>
            @endforelse
        </ul>
    </div>

    @php
        $estadoBadge = function ($ev) {
            $estado = $ev->estado_revision_lider?->value ?? $ev->estado_revision_lider;
            return match ($estado) {
                'aprobado' => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>',
                'rechazado' => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>',
                default => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>',
            };
        };
    @endphp

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Formulación <span class="text-xs font-normal text-slate-500">(30% del avance)</span></h3>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($formulacion as $ev)
            <li class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    {!! $estadoBadge($ev) !!}
                    @if($ev->archivo)
                    <a href="{{ route('lider-proyecto.evidencias.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    <form action="{{ route('lider-proyecto.evidencias.destroy', $ev) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin evidencia de formulación todavía.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Ejecución <span class="text-xs font-normal text-slate-500">(50% del avance)</span></h3>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($ejecucion as $ev)
            <li class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    {!! $estadoBadge($ev) !!}
                    @if($ev->archivo)
                    <a href="{{ route('lider-proyecto.evidencias.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    <form action="{{ route('lider-proyecto.evidencias.destroy', $ev) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin evidencia de ejecución todavía.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Producto final <span class="text-xs font-normal text-slate-500">(20% del avance)</span></h3>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($productoFinal as $ev)
            <li class="px-5 py-3 flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium text-slate-800">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    {!! $estadoBadge($ev) !!}
                    @if($ev->archivo)
                    <a href="{{ route('lider-proyecto.evidencias.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    <form action="{{ route('lider-proyecto.evidencias.destroy', $ev) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin evidencia de producto final todavía.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
