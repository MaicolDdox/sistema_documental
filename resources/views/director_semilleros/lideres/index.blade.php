@extends('director_semilleros.layout')

@section('title', 'Líderes de Semillero')
@section('header', 'Directorio de Líderes')

@section('content')
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
    <a href="{{ route('dir-sem.lideres.create') }}" class="sgd-btn-primary py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        + Nuevo Líder
    </a>
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
                        <div class="flex items-center justify-end gap-1">
                            @can('usuarios.listar')
                            <a href="{{ route('dir-sem.lideres.show', $lider) }}" class="p-2 text-slate-500 hover:text-[#39A900] hover:bg-[#39A900]/10 rounded-lg transition-all duration-200" title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                            @endcan
                            @can('usuarios.editar')
                            <a href="{{ route('dir-sem.lideres.edit', $lider) }}" class="p-2 text-slate-500 hover:text-[#39A900] hover:bg-[#39A900]/10 rounded-lg transition-all duration-200" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                            </a>
                            <form action="{{ route('dir-sem.lideres.destroy', $lider) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este líder?');">
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
@endsection
