@extends('lider_semillero.layout')

@section('title', 'Aprendices')
@section('header', '')

@section('content')
@if(!$semillero)
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Aprendices</h1>
    <p class="text-sm text-slate-500 mt-0.5">Registro y vinculación de aprendices</p>
</div>
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
<div x-data="{ modalRegistrar: false, modalVincular: false, userIdVincular: null, nombreVincular: '' }" x-init="@if($errors->has('numero_documento') || old('numero_documento')) $nextTick(() => { modalRegistrar = true; }); @endif">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Aprendices</h1>
        <p class="text-sm text-slate-500 mt-0.5">Registro y vinculación de aprendices</p>
    </div>
    <button type="button" @click="modalRegistrar = true" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Registrar Aprendiz
    </button>
</div>
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 mb-4 text-sm text-amber-800">
    <strong>users.estado = inactivo</strong> · Sin acceso al sistema · Solo como autores en proyectos/productos
</div>

<form action="{{ route('lider-sem.aprendices') }}" method="get" class="mb-4 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-slate-700 mb-1">Buscar aprendiz por documento</label>
        <input type="text" name="documento" value="{{ request('documento') }}" placeholder="N° de documento de identidad"
            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
    </div>
    <button type="submit" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">Buscar</button>
</form>

<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Aprendiz</th>
                    <th class="text-left">Documento</th>
                    <th class="text-left">Proyecto vinculado</th>
                    <th class="text-left">Estado vínculo</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aprendices as $ap)
                @php
                    $nombre = $ap->person?->nombre_completo ?? $ap->email ?? 'Sin nombre';
                    $iniciales = $ap->initials();
                    $doc = $ap->numero_documento ?? '—';
                @endphp
                <tr>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center text-sm font-semibold shrink-0">{{ $iniciales ?: '?' }}</span>
                            <span class="font-medium text-slate-800">{{ $nombre }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $doc }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $ap->proyecto_vinculado ?? 'Sin proyecto' }}</td>
                    <td class="px-5 py-3">
                        @if($ap->tiene_vinculo ?? false)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✓ Activo</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">▲ Sin proyecto</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($ap->tiene_vinculo && ($ap->vinculo_activo ?? null))
                        <form action="{{ route('lider-sem.aprendices.desvincular', $ap->vinculo_activo) }}" method="post" class="inline" onsubmit="return confirm('¿Desvincular a este aprendiz del proyecto?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition">Desvincular</button>
                        </form>
                        @else
                        <button type="button"
                            @click="userIdVincular = {{ $ap->id }}; nombreVincular = '{{ e($nombre) }}'; modalVincular = true"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition">Vincular proyecto</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                        @if(request('documento'))
                        No se encontraron aprendices con ese documento.
                        @else
                        No hay aprendices en el semillero.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
    Al desvincular se ejecuta <strong>UPDATE activo → falso</strong>. Nunca DELETE — el historial siempre se conserva.
</div>

{{-- Modal Registrar Aprendiz --}}
<template x-teleport="body">
    <div x-show="modalRegistrar" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalRegistrar = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Registrar Aprendiz</h3>
                <button type="button" @click="modalRegistrar = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-600 mb-4">Ingresa el número de documento del usuario a agregar como aprendiz del semillero.</p>
            <form action="{{ route('lider-sem.aprendices.registrar') }}" method="post">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">N° de documento *</label>
                    <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]"
                        placeholder="N° de documento de identidad">
                    @error('numero_documento')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="modalRegistrar = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</template>

{{-- Modal Vincular proyecto --}}
<template x-teleport="body">
    <div x-show="modalVincular" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalVincular = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Vincular proyecto</h3>
                <button type="button" @click="modalVincular = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-600 mb-2" x-show="nombreVincular">Aprendiz: <strong x-text="nombreVincular"></strong></p>
            <form action="{{ route('lider-sem.aprendices.vincular') }}" method="post" class="space-y-4">
                @csrf
                <input type="hidden" name="user_id" :value="userIdVincular">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Proyecto *</label>
                    <select name="project_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
                        <option value="">Seleccione proyecto...</option>
                        @foreach($proyectosDelSemillero ?? [] as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="modalVincular = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">Vincular</button>
                </div>
            </form>
        </div>
    </div>
</template>
</div>
@endif
@endsection
