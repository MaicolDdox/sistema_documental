@extends('layouts.sgd')

@section('title', 'Grupos de Investigación')
@section('header', '')

@section('content')
<div x-data="{ modalNuevoGrupo: @json($errors->any() && old('_from_modal')) }">
{{-- Breadcrumbs --}}
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#39A900]">Administración</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Grupos de Investigación</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-0.5">Grupos de Investigación</h2>
<p class="text-sm text-slate-500 mb-6">Crea grupos con datos básicos; el director asignado completa la descripción, el logo y la línea de investigación.</p>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('admin.grupos-investigacion.index') }}" class="flex gap-2 flex-1 max-w-md">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar grupo..."
               class="sgd-input-focus w-full pl-3 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
            Buscar
        </button>
    </form>
    @can('grupos_investigacion.crear')
    <button type="button" @click="modalNuevoGrupo = true" class="sgd-btn-primary py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        + Nuevo Grupo
    </button>
    @endcan
</div>

<div class="sgd-table-card bg-white">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Código</th>
                    <th class="text-left">Director</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grupos as $grupo)
                @php
                    $director = $grupo->director;
                    $directorName = $director && $director->person
                        ? trim(($director->person->primer_nombre ?? '').' '.($director->person->primer_apellido ?? ''))
                        : ($director->email ?? '—');
                    if ($directorName === '') { $directorName = $director->email ?? '—'; }
                @endphp
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $grupo->nombre }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">{{ $grupo->codigo }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $directorName }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($grupo->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.grupos-investigacion.show', $grupo) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 mr-3">Ver</a>
                        @can('grupos_investigacion.editar')
                        <a href="{{ route('admin.grupos-investigacion.edit', $grupo) }}" class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Editar</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-slate-500">No se encontraron grupos de investigación.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($grupos->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/30">
        {{ $grupos->links() }}
    </div>
    @endif
</div>

{{-- Modal: Nuevo Grupo --}}
@can('grupos_investigacion.crear')
<div x-show="modalNuevoGrupo" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="modalNuevoGrupo" @click.self="modalNuevoGrupo = false" class="fixed inset-0 bg-black/40" x-transition></div>
        <div x-show="modalNuevoGrupo" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
            <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <h3 class="text-lg font-semibold text-slate-900">Nuevo Grupo de Investigación</h3>
                <button type="button" @click="modalNuevoGrupo = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.grupos-investigacion.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="_from_modal" value="1">
                <div class="space-y-4">
                    <div>
                        <label for="modal_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre del Grupo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_nombre" value="{{ old('nombre') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('nombre') border-red-300 @enderror">
                        @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="modal_codigo" class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo" id="modal_codigo" value="{{ old('codigo') }}" maxlength="50" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('codigo') border-red-300 @enderror">
                        @error('codigo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-slate-500">Podrás asignar un director desde "Vinculaciones grupo de investigación". La descripción, el logo y la línea de investigación los completa el director asignado.</p>
                </div>
                <div class="flex gap-3 justify-end pt-5 mt-5 border-t border-slate-100">
                    <button type="button" @click="modalNuevoGrupo = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar Grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
</div>
@endsection
