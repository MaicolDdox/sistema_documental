@extends('director_semilleros.layout')

@section('title', 'Detalle del Líder')
@section('header', '')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <a href="{{ route('dir-sem.lideres.index') }}" class="hover:text-[#39A900]">Líderes de Semillero</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Detalle</span>
</nav>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-900">Detalle del Líder</h2>
    <a href="{{ route('dir-sem.lideres.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Volver
    </a>
</div>

@php
    $nombreCompleto = $lider->person ? trim(($lider->person->primer_nombre ?? '') . ' ' . ($lider->person->primer_apellido ?? '')) : $lider->email;
    if ($nombreCompleto === '') {
        $nombreCompleto = $lider->email;
    }
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden max-w-2xl">
    <div class="p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center font-bold text-lg">
                {{ $lider->initials() }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $nombreCompleto }}</h3>
                <p class="text-sm text-slate-500">{{ $lider->email }}</p>
                @if($lider->estado === \App\Enums\EstadoEnum::Activo)
                    <span class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-green-700"><span class="w-2 h-2 rounded-full bg-green-500"></span> Activo</span>
                @else
                    <span class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-red-600"><span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo</span>
                @endif
            </div>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-slate-500 font-medium">Documento</dt>
                <dd class="text-slate-900 mt-0.5">{{ $lider->tipo_documento && is_object($lider->tipo_documento) ? $lider->tipo_documento->value : 'CC' }} {{ $lider->numero_documento }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 font-medium">Semilleros asignados</dt>
                <dd class="mt-0.5">
                    @forelse($lider->ledSeedlings as $s)
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#39A900]/15 text-[#2d8500] mr-1">{{ $s->nombre }}</span>
                    @empty
                        <span class="text-slate-400">Ninguno</span>
                    @endforelse
                </dd>
            </div>
        </dl>
        <div class="mt-6 pt-4 border-t border-slate-100 flex gap-2">
            @can('usuarios.editar')
            <a href="{{ route('dir-sem.lideres.edit', $lider) }}" class="inline-flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white font-medium py-2 px-4 rounded-lg text-sm">
                Editar
            </a>
            @endcan
            <a href="{{ route('dir-sem.lideres.index') }}" class="inline-flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium py-2 px-4 rounded-lg text-sm">
                Volver a la lista
            </a>
        </div>
    </div>
</div>
@endsection
