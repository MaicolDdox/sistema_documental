@extends('layouts.sgd')

@section('title', 'Info del Semillero')
@section('header', '')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Info del Semillero</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                @if($semillero)
                    Consulta la información registrada por el Director para {{ $semillero->nombre }}
                @else
                    Información de tu semillero
                @endif
            </p>
        </div>
    </div>

    @if(!$semillero)
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
            <p class="text-sm text-slate-600 mb-6 max-w-md mx-auto">
                No tienes un semillero asignado como líder. El Director de Semilleros debe asignarte como líder de un semillero para que puedas consultarlo aquí.
            </p>
            <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Dashboard
            </a>
        </div>
    </div>
    @else
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
            <h2 class="text-base font-semibold text-slate-900 font-heading">Información del Semillero</h2>
            <p class="text-xs text-slate-500 mt-0.5">Vista de solo lectura para Líder de Semillero.</p>
        </div>

        <div class="p-6 space-y-6">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <label class="block text-sm font-medium text-slate-700">Logo</label>
                <div class="w-24 h-24 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                    @if(!empty($semillero->logo))
                        <img src="{{ asset('storage/' . $semillero->logo) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <svg class="w-10 h-10 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del semillero</label>
                <input type="text" value="{{ $semillero->nombre }}" readonly
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-600 bg-slate-50 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                <input type="text" value="{{ $semillero->codigo ?? '—' }}" readonly
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-600 bg-slate-50 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <textarea rows="4" readonly
                          class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-600 bg-slate-50 cursor-not-allowed">{{ $semillero->descripccion ?? '—' }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo de investigación</label>
                <input type="text" value="{{ $semillero->researchGroup?->nombre ?? '—' }}" readonly
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-600 bg-slate-50 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                <div class="flex items-center gap-2 border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50">
                    @if(($semillero->estado->value ?? '') === 'activo')
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-sm text-slate-600">Activo</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-sm text-slate-600">Inactivo</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
