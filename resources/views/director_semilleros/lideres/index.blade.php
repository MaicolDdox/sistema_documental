@extends('layouts.sgd')

@section('title', 'Líderes de Semillero')
@section('header', 'Directorio de Líderes')

@section('content')
@php
    $baseUrlLideres = url('director-semilleros/lideres');
@endphp
<div x-data="{
    modalNuevoLider: @json($errors->any() && old('_from_modal')),
    modalDetalle: false,
    modalEditar: false,
    modalEliminar: false,
    detalleLider: null,
    editLider: null,
    deleteLider: null
}">
{{-- Breadcrumbs --}}
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Líderes de Semillero</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-1">Líderes de Semillero</h2>
<p class="text-sm text-slate-500 mb-4">Solo usuarios con rol Líder de Semillero de tu centro de formación.</p>

{{-- Barra: búsqueda + botón --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('dir-sem.lideres.index') }}" class="flex gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar líder..."
                   class="sgd-input-focus w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        </div>
        <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
            Buscar
        </button>
    </form>
    @can('usuarios.crear_lider_semillero')
    <button type="button" @click="modalNuevoLider = true" class="sgd-btn-primary py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        + Nuevo Líder
    </button>
    @endcan
</div>

{{-- Card: Líderes del Centro --}}
<div class="sgd-table-card bg-white">
    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
        <h3 class="text-base font-semibold text-slate-900">Líderes del Centro</h3>
        <p class="text-xs text-slate-500 mt-1">Gestión de usuarios con rol de Líder de Semillero de tu centro de formación.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Documento</th>
                    <th class="text-left">Email</th>
                    <th class="text-left">Semillero asignado</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lideres as $lider)
                @php
                    $nombreCompleto = $lider->person
                        ? trim(($lider->person->primer_nombre ?? '') . ' ' . ($lider->person->primer_apellido ?? ''))
                        : $lider->email;
                    if ($nombreCompleto === '') {
                        $nombreCompleto = $lider->email;
                    }
                    $semilleroAsignado = $lider->ledSeedlings->first();
                    $docTipo = $lider->tipo_documento ? (\is_object($lider->tipo_documento) ? $lider->tipo_documento->value : $lider->tipo_documento) : 'CC';
                    $liderDetalle = [
                        'id' => $lider->id,
                        'nombre' => $nombreCompleto,
                        'iniciales' => $lider->initials(),
                        'documento' => $docTipo . ' ' . $lider->numero_documento,
                        'email' => $lider->email,
                        'semilleros' => $lider->ledSeedlings->pluck('nombre')->toArray(),
                        'estado' => $lider->estado === \App\Enums\EstadoEnum::Activo ? 'Activo' : 'Inactivo',
                        'estado_activo' => $lider->estado === \App\Enums\EstadoEnum::Activo,
                    ];
                    $liderEdit = [
                        'id' => $lider->id,
                        'primer_nombre' => $lider->person?->primer_nombre ?? '',
                        'primer_apellido' => $lider->person?->primer_apellido ?? '',
                        'email' => $lider->email,
                        'numero_documento' => $lider->numero_documento ?? '',
                        'estado' => $lider->estado?->value ?? 'activo',
                    ];
                @endphp
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center font-bold text-sm shrink-0">
                                {{ $lider->initials() }}
                            </div>
                            <p class="text-sm font-medium text-slate-900">{{ $nombreCompleto }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-sm">
                        {{ $docTipo }} {{ $lider->numero_documento }}
                    </td>
                    <td class="px-4 py-3 text-slate-700 text-sm">{{ $lider->email }}</td>
                    <td class="px-4 py-3">
                        @if($semilleroAsignado)
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#39A900]/15 text-[#2d8500]">
                            {{ $semilleroAsignado->nombre }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($lider->estado === \App\Enums\EstadoEnum::Activo)
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
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            @can('usuarios.listar')
                            <button type="button" @click='detalleLider = @json($liderDetalle); modalDetalle = true' class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            @endcan
                            @can('usuarios.editar')
                            <button type="button" @click='editLider = @json($liderEdit); modalEditar = true' class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                            </button>
                            <form action="{{ route('dir-sem.lideres.toggle-estado', $lider) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-[#39A900] hover:bg-green-50 transition-colors cursor-pointer" title="{{ $lider->estado === \App\Enums\EstadoEnum::Activo ? 'Desactivar' : 'Activar' }}">
                                    @if($lider->estado === \App\Enums\EstadoEnum::Activo)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            </form>
                            <button type="button" @click='deleteLider = { id: {{ $lider->id }}, nombre: {{ Js::from($nombreCompleto) }} }; modalEliminar = true' class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <svg class="w-14 h-14 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                        <p class="text-slate-500">No se encontraron líderes de semillero.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lideres->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/30">
        {{ $lideres->links() }}
    </div>
    @endif
</div>

    {{-- Modal: Nuevo Líder --}}
    @can('usuarios.crear_lider_semillero')
    <div x-show="modalNuevoLider" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevoLider" @click.self="modalNuevoLider = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevoLider" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#39A900]/10 text-[#39A900] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Registrar nuevo líder</h3>
                            <p class="text-xs text-slate-500">La contraseña temporal se generará automáticamente.</p>
                        </div>
                    </div>
                    <button type="button" @click="modalNuevoLider = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('dir-sem.lideres.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="_from_modal" value="1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="modal_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="modal_nombre" value="{{ old('nombre') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('nombre') border-red-300 @enderror">
                            @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="modal_apellido" class="block text-sm font-medium text-slate-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" name="apellido" id="modal_apellido" value="{{ old('apellido') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('apellido') border-red-300 @enderror">
                            @error('apellido') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="modal_numero_documento" class="block text-sm font-medium text-slate-700 mb-1">No. de Documento <span class="text-red-500">*</span></label>
                            <input type="text" name="numero_documento" id="modal_numero_documento" value="{{ old('numero_documento') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('numero_documento') border-red-300 @enderror">
                            @error('numero_documento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="modal_email" class="block text-sm font-medium text-slate-700 mb-1">Correo <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="modal_email" value="{{ old('email') }}" required placeholder="email@sena.edu.co" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('email') border-red-300 @enderror">
                            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="modal_semillero_id" class="block text-sm font-medium text-slate-700 mb-1">Asignar a Semillero (opcional)</label>
                        <select name="semillero_id" id="modal_semillero_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Selecciona un semillero (o asigna después)...</option>
                            @foreach($semilleros ?? [] as $semillero)
                                <option value="{{ $semillero->id }}" {{ old('semillero_id') == $semillero->id ? 'selected' : '' }}>{{ $semillero->nombre }}</option>
                            @endforeach
                        </select>
                        @error('semillero_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @can('usuarios.asignar_credenciales')
                    <div class="mb-5 p-4 bg-green-50 border border-green-100 rounded-lg flex items-start gap-3">
                        <input type="checkbox" name="enviar_credenciales" id="modal_enviar_credenciales" value="1" {{ old('enviar_credenciales', true) ? 'checked' : '' }} class="mt-1 w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-2 focus:ring-[#39A900]">
                        <label for="modal_enviar_credenciales" class="text-sm text-slate-700">Enviar credenciales por correo al líder.</label>
                    </div>
                    @endcan
                    <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="modalNuevoLider = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Registrar Líder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal: Ver detalle --}}
    <div x-show="modalDetalle && detalleLider" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalle" @click.self="modalDetalle = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalDetalle" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-slate-900">Detalle del líder</h3>
                    <button type="button" @click="modalDetalle = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <template x-if="detalleLider">
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-14 h-14 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center font-bold text-lg" x-text="detalleLider.iniciales"></div>
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900" x-text="detalleLider.nombre"></h4>
                                    <p class="text-sm text-slate-500" x-text="detalleLider.email"></p>
                                    <span class="inline-flex items-center gap-1 mt-1 text-xs font-medium"
                                          :class="detalleLider.estado_activo ? 'text-green-700' : 'text-red-600'">
                                        <span class="w-2 h-2 rounded-full" :class="detalleLider.estado_activo ? 'bg-green-500' : 'bg-red-500'"></span>
                                        <span x-text="detalleLider.estado"></span>
                                    </span>
                                </div>
                            </div>
                            <dl class="grid grid-cols-1 gap-4 text-sm">
                                <div>
                                    <dt class="text-slate-500 font-medium">Documento</dt>
                                    <dd class="text-slate-900 mt-0.5" x-text="detalleLider.documento"></dd>
                                </div>
                                <div>
                                    <dt class="text-slate-500 font-medium">Semilleros asignados</dt>
                                    <dd class="mt-0.5">
                                        <template x-if="detalleLider.semilleros && detalleLider.semilleros.length">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="s in detalleLider.semilleros" :key="s">
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#39A900]/15 text-[#2d8500]" x-text="s"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!detalleLider.semilleros || !detalleLider.semilleros.length">
                                            <span class="text-slate-400">Ninguno</span>
                                        </template>
                                    </dd>
                                </div>
                            </dl>
                            <div class="mt-6 pt-4 border-t border-slate-100">
                                <button type="button" @click="modalDetalle = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Editar líder --}}
    @can('usuarios.editar')
    <div x-show="modalEditar && editLider" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditar" @click.self="modalEditar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditar" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-slate-900">Editar líder</h3>
                    <button type="button" @click="modalEditar = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <template x-if="editLider">
                        <form :action="'{{ $baseUrlLideres }}/' + editLider.id" method="POST" class="space-y-5">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="edit_primer_nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" name="primer_nombre" id="edit_primer_nombre" x-model="editLider.primer_nombre" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                </div>
                                <div>
                                    <label for="edit_primer_apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                                    <input type="text" name="primer_apellido" id="edit_primer_apellido" x-model="editLider.primer_apellido" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                </div>
                            </div>
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="edit_email" x-model="editLider.email" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                            <div>
                                <label for="edit_numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Número de documento <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_documento" id="edit_numero_documento" x-model="editLider.numero_documento" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                            <div>
                                <label for="edit_estado" class="block text-sm font-medium text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                                <select name="estado" id="edit_estado" x-model="editLider.estado" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                                <button type="button" @click="modalEditar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar cambios</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal: Confirmar eliminar --}}
    @can('usuarios.editar')
    <div x-show="modalEliminar && deleteLider" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEliminar" @click.self="modalEliminar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEliminar" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Eliminar líder</h3>
                <p class="text-sm text-slate-600 mb-4">¿Está seguro de eliminar a <strong x-text="deleteLider && deleteLider.nombre"></strong>? Esta acción no se puede deshacer.</p>
                <template x-if="deleteLider">
                    <form :action="'{{ $baseUrlLideres }}/' + deleteLider.id" method="POST" class="flex gap-3 justify-end">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="modalEliminar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Eliminar</button>
                    </form>
                </template>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
