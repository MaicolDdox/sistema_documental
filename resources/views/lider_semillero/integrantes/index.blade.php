@extends('layouts.sgd')

@section('title', 'Integrantes del Semillero')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Integrantes del Semillero</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        @if($semillero)
            {{ $semillero->nombre }} — {{ $integrantes->count() }} {{ $integrantes->count() === 1 ? 'registro' : 'registros' }}
        @else
            Sin semillero asignado
        @endif
    </p>
</div>

@if(!$semillero)
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">
            No tienes un semillero asignado como líder. No hay integrantes que mostrar.
        </p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">
            Volver al Dashboard
        </a>
    </div>
</div>
@elseif($integrantes->isEmpty())
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-12 text-center">
        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">No hay integrantes</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">
            Este semillero aún no tiene integrantes registrados. El Director de Semilleros puede agregar miembros al semillero.
        </p>
    </div>
</div>
@else
<div x-data="{ showDetailId: null }">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($integrantes as $integrante)
        @php
            $nombre = $integrante->person?->nombre_completo ?? $integrante->email ?? 'Sin nombre';
            $iniciales = $integrante->initials();
            $doc = $integrante->numero_documento ?? '—';
            $roles = $integrante->roles_texto ?? 'Sin rol asignado';
        @endphp
        <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-5 hover:border-[#39A900]/30 transition-colors">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-[#39A900]/20 flex items-center justify-center mb-3 shrink-0">
                    <span class="text-lg font-bold text-[#39A900]">{{ $iniciales }}</span>
                </div>
                <h3 class="font-semibold text-slate-900 mb-1">{{ $nombre }}</h3>
                <p class="text-xs text-slate-500 mb-3">Doc: {{ $doc }}</p>
                <p class="text-xs text-slate-600 mb-3">
                    <span class="font-semibold">Rol:</span> {{ $roles }}
                </p>
                @if($integrante->con_proyecto ?? false)
                <p class="flex items-center justify-center gap-1.5 text-sm text-green-700 mb-4">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Con proyecto
                </p>
                @else
                <p class="flex items-center justify-center gap-1.5 text-sm text-amber-700 mb-4">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Sin proyecto
                </p>
                @endif
                <button type="button"
                        @click="showDetailId = {{ $integrante->id }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Ver
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Modal detalle integrante --}}
    <div x-show="showDetailId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-slate-200"
             @click.self="showDetailId = null">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Detalle del integrante</h2>
                <button type="button" @click="showDetailId = null" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                @foreach($integrantes as $integrante)
                @php
                    $p = $integrante->person;
                    $nombreCompleto = $p?->nombre_completo ?? $integrante->email ?? 'Sin nombre';
                    $doc = $integrante->numero_documento ?? '—';
                    $email = $integrante->email ?? '—';
                    $telefono = $p?->telefono ?? '—';
                    $roles = $integrante->roles_texto ?? 'Sin rol asignado';
                @endphp
                <div x-show="showDetailId === {{ $integrante->id }}" x-cloak class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-[#39A900]/20 flex items-center justify-center">
                            <span class="text-lg font-bold text-[#39A900]">{{ $integrante->initials() }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $nombreCompleto }}</h3>
                            <p class="text-xs text-slate-500">Documento: {{ $doc }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Correo</p>
                            <p class="text-sm font-medium text-slate-700 break-all">{{ $email }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Teléfono</p>
                            <p class="text-sm font-medium text-slate-700">{{ $telefono }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-400 mb-0.5">Rol en el sistema</p>
                        <p class="text-sm font-medium text-slate-700">{{ $roles }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-400 mb-0.5">Estado en el semillero</p>
                        @if($integrante->con_proyecto ?? false)
                            <p class="text-sm font-medium text-green-700 flex items-center gap-1">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Con proyecto
                            </p>
                        @else
                            <p class="text-sm font-medium text-amber-700 flex items-center gap-1">
                                <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Sin proyecto
                            </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
@endsection
