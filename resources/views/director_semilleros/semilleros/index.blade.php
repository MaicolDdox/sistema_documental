@extends('director_semilleros.layout')

@section('title', 'Gestión de Semilleros')
@section('header', 'Semilleros de Investigación')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <!-- Buscador y Filtros -->
    <form method="GET" action="{{ route('dir-sem.semilleros.index') }}" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o código..." 
                   class="w-full sm:w-64 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        </div>
        <div>
            <select name="estado" class="w-full sm:w-auto border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                <option value="">Todos los estados</option>
                <option value="{{ \App\Enums\EstadoEnum::Activo->value }}" {{ request('estado') === \App\Enums\EstadoEnum::Activo->value ? 'selected' : '' }}>Activo</option>
                <option value="{{ \App\Enums\EstadoEnum::Inactivo->value }}" {{ request('estado') === \App\Enums\EstadoEnum::Inactivo->value ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            Filtrar
        </button>
        @if(request()->hasAny(['search', 'estado']))
        <a href="{{ route('dir-sem.semilleros.index') }}" class="text-xs text-slate-500 hover:text-red-500 self-center ml-2">Limpiar</a>
        @endif
    </form>

    <!-- Botón Nuevo -->
    @can('semilleros.crear')
    <a href="{{ route('dir-sem.semilleros.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center justify-center gap-2 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nuevo Semillero
    </a>
    @endcan
</div>

<!-- Tabla de Datos -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Código / Nombre</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Líder</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Integrantes</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyectos</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($semilleros as $semillero)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <!-- Avatar Semillero -->
                            <div class="w-10 h-10 rounded-lg bg-green-50 text-[#39A900] flex items-center justify-center flex-shrink-0 font-bold text-sm">
                                {{ substr($semillero->nombre, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $semillero->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $semillero->codigo ?? 'Sin código' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5" title="{{ $semillero->researchGroup->nombre ?? '' }}">
                                    {{ Str::limit($semillero->researchGroup->nombre ?? 'Sin grupo', 30) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                {{ strtoupper(substr($semillero->leader->email ?? 'L', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm text-slate-700">{{ $semillero->leader->person->primer_nombre ?? '' }} {{ $semillero->leader->person->primer_apellido ?? '' }}</p>
                                <p class="text-xs text-slate-400">{{ $semillero->leader->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-medium text-xs">
                            {{ $semillero->members->count() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-medium text-xs">
                            {{ $semillero->projects->count() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($semillero->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Activo
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div> Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('semilleros.ver_detalle')
                            <a href="{{ route('dir-sem.semilleros.show', $semillero) }}" class="p-1.5 text-slate-400 hover:text-[#39A900] hover:bg-green-50 rounded-lg transition-colors" title="Ver Detalle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </a>
                            @endcan

                            @can('semilleros.editar')
                            <a href="{{ route('dir-sem.semilleros.edit', $semillero) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                            </a>
                            @endcan

                            @can('semilleros.activar_desactivar')
                            <form action="{{ route('dir-sem.semilleros.toggle-estado', $semillero) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirma cambiar el estado de este semillero?');">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Cambiar Estado">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        <p>No se encontraron semilleros.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($semilleros->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
        {{ $semilleros->links() }}
    </div>
    @endif
</div>
@endsection
