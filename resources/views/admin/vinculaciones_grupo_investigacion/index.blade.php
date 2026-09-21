@extends('layouts.sgd')

@section('title', 'Vincular Grupo y Director')
@section('header', 'Vinculación Grupo de Investigación ↔ Director')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#39A900]">Administración</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Vincular Grupo y Director</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-1">Vinculación Grupo de Investigación ↔ Director</h2>
<p class="text-sm text-slate-500 mb-6">Asigna o quita el director de cada grupo de investigación de tu centro de formación.</p>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('admin.vinculaciones-grupo-investigacion.index') }}" class="flex gap-2 flex-1 max-w-md">
        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar grupo..."
               class="sgd-input-focus w-full pl-3 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
            Buscar
        </button>
    </form>
</div>

<div class="sgd-table-card bg-white">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Grupo</th>
                    <th class="text-left">Código</th>
                    <th class="text-left">Director actual</th>
                    <th class="text-left">Nuevo director</th>
                    <th class="text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grupos as $grupo)
                    @php
                        $director = $grupo->director;
                        $directorName = $director && $director->person
                            ? trim(($director->person->primer_nombre ?? '').' '.($director->person->primer_apellido ?? ''))
                            : ($director->email ?? 'Sin director');
                        if ($directorName === '') { $directorName = $director->email ?? 'Sin director'; }
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-slate-900 font-medium">{{ $grupo->nombre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $grupo->codigo }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $directorName }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.vinculaciones-grupo-investigacion.update', $grupo) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="director_id" class="min-w-[260px] border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                    <option value="">Sin director asignado</option>
                                    @foreach($directores as $dir)
                                        @php
                                            $nombreDir = $dir->person
                                                ? trim(($dir->person->primer_nombre ?? '').' '.($dir->person->primer_apellido ?? ''))
                                                : $dir->email;
                                            if ($nombreDir === '') { $nombreDir = $dir->email; }
                                            $puedeMostrarse = ((int) $grupo->director_id === (int) $dir->id)
                                                || !in_array($dir->id, $directoresYaVinculadosIds ?? [], true);
                                        @endphp
                                        @if($puedeMostrarse)
                                        <option value="{{ $dir->id }}" {{ (string) old('director_id', $grupo->director_id) === (string) $dir->id ? 'selected' : '' }}>
                                            {{ $nombreDir }} ({{ $dir->email }})
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                        </td>
                        <td class="px-4 py-3 text-right">
                                <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-xs font-semibold">
                                    Guardar vínculo
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">No se encontraron grupos de investigación.</td>
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
@endsection
