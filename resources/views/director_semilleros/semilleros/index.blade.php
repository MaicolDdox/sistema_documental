@extends('director_semilleros.layout')

@section('title', 'Semilleros de Investigación')
@section('header', '')

@section('content')
{{-- Breadcrumbs --}}
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Semilleros</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-0.5">Semilleros de Investigación</h2>
<p class="text-sm text-slate-500 mb-6">Tabla: semilleros — Solo semilleros asignados a tu dirección.</p>

{{-- Tabs --}}
@php $tab = $tab ?? 'todos'; @endphp
<div class="flex gap-6 border-b-2 border-slate-100 mb-4">
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'todos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'todos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Todos
    </a>
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'activos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'activos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Activos
    </a>
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'inactivos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'inactivos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Inactivos
    </a>
</div>

{{-- Buscar + Nuevo Semillero --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('dir-sem.semilleros.index') }}" class="flex gap-2 flex-1 max-w-md">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar semillero..."
                   class="sgd-input-focus w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        </div>
        <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
            Buscar
        </button>
    </form>
    @can('semilleros.crear')
    <a href="{{ route('dir-sem.semilleros.create') }}" class="sgd-btn-primary py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        + Nuevo Semillero
    </a>
    @endcan
</div>

{{-- Tabla --}}
<div class="sgd-table-card bg-white">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Código</th>
                    <th class="text-left">Líder</th>
                    <th class="text-left">Grupo Inv.</th>
                    <th class="text-center">Integrantes</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semilleros as $semillero)
                <tr>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-slate-900">{{ $semillero->nombre }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                            {{ $semillero->codigo ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $leader = $semillero->leader;
                            $leaderName = $leader && $leader->person
                                ? trim(($leader->person->primer_nombre ?? '') . ' ' . ($leader->person->primer_apellido ?? ''))
                                : ($leader->email ?? '—');
                            if ($leaderName === '') {
                                $leaderName = $leader->email ?? '—';
                            }
                        @endphp
                        <p class="text-sm text-slate-700">{{ $leaderName }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($semillero->researchGroup)
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#39A900]/15 text-[#2d8500]">
                            {{ $semillero->researchGroup->nombre }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-medium text-blue-600">{{ $semillero->members->count() }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($semillero->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @can('semilleros.ver_detalle')
                            <a href="{{ route('dir-sem.semilleros.show', $semillero) }}" class="p-2 rounded-lg {{ $semillero->estado === \App\Enums\EstadoEnum::Activo ? 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' : 'text-red-400 hover:text-red-600 hover:bg-red-50' }}" title="Ver detalle">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/></svg>
                            </a>
                            @endcan
                            @can('semilleros.editar')
                            <a href="{{ route('dir-sem.semilleros.edit', $semillero) }}" class="p-2 text-slate-500 hover:text-[#39A900] hover:bg-[#39A900]/10 rounded-lg transition-all duration-200" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                            </a>
                            <form action="{{ route('dir-sem.semilleros.destroy', $semillero) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este semillero?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        <p class="text-slate-500">No se encontraron semilleros.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($semilleros->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/30">
        {{ $semilleros->links() }}
    </div>
    @endif
</div>
@endsection
