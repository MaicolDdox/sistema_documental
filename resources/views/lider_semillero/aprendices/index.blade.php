@extends('layouts.sgd')

@section('title', 'Aprendices')
@section('header', '')

@section('content')
@if(!$semillero)
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Aprendices</h1>
    <p class="text-sm text-slate-500 mt-0.5">Aprendices del semillero (solo visualización)</p>
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
<div x-data="{ showVincularId: null }">
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Aprendices</h1>
    <p class="text-sm text-slate-500 mt-0.5">Aprendices del semillero. Puedes vincularlos a proyectos del semillero.</p>
</div>
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

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
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                    @click="showVincularId = {{ (int) $ap->id }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 hover:bg-slate-50 text-slate-700">
                                @if($ap->tiene_vinculo ?? false)
                                    Cambiar proyecto
                                @else
                                    Vincular
                                @endif
                            </button>

                            @if(($ap->tiene_vinculo ?? false) && ($ap->vinculo_activo?->id))
                                <form action="{{ route('lider-sem.aprendices.desvincular', $ap->vinculo_activo->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-red-200 hover:bg-red-50 text-red-700">
                                        Desvincular
                                    </button>
                                </form>
                            @endif
                        </div>
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

{{-- Modal vincular aprendiz a proyecto --}}
<div x-show="showVincularId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-slate-200"
         @click.self="showVincularId = null">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Vincular aprendiz a proyecto</h2>
            <button type="button" @click="showVincularId = null" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('lider-sem.aprendices.vincular') }}" method="post" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="user_id" :value="showVincularId">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proyecto del semillero *</label>
                <select name="project_id" required
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
                    <option value="">Seleccione...</option>
                    @foreach($proyectosDelSemillero as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rounded-xl bg-sky-50 border border-sky-200 px-4 py-3 text-xs text-sky-800">
                El aprendiz quedará como integrante activo de este proyecto (tabla proyecto_autores).
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showVincularId = null" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">
                    Vincular
                </button>
            </div>
        </form>
    </div>
</div>

@endif
{{-- cierre x-data --}}
</div>
@endsection
