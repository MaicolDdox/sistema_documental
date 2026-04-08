@extends('layouts.sgd')

@section('title', 'Vincular Semillero y Líder')
@section('header', 'Vinculación Semillero ↔ Líder')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Vincular Semillero y Líder</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-1">Vinculación Semillero ↔ Líder</h2>
<p class="text-sm text-slate-500 mb-6">Asigna o quita el líder de cada semillero de tu centro de formación.</p>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('dir-sem.vinculaciones.index') }}" class="flex gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar semillero..."
                   class="sgd-input-focus w-full pl-3 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        </div>
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
                    <th class="text-left">Semillero</th>
                    <th class="text-left">Código</th>
                    <th class="text-left">Líder actual</th>
                    <th class="text-left">Nuevo líder</th>
                    <th class="text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semilleros as $semillero)
                    @php
                        $leader = $semillero->leader;
                        $leaderName = $leader && $leader->person
                            ? trim(($leader->person->primer_nombre ?? '') . ' ' . ($leader->person->primer_apellido ?? ''))
                            : ($leader->email ?? 'Sin líder');
                        if ($leaderName === '') {
                            $leaderName = $leader->email ?? 'Sin líder';
                        }
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-slate-900 font-medium">{{ $semillero->nombre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $semillero->codigo ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $leaderName }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('dir-sem.vinculaciones.update', $semillero) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="lider_id" class="min-w-[260px] border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                    <option value="">Sin líder asignado</option>
                                    @foreach($lideres as $lider)
                                        @php
                                            $nombreLider = $lider->person
                                                ? trim(($lider->person->primer_nombre ?? '') . ' ' . ($lider->person->primer_apellido ?? ''))
                                                : $lider->email;
                                            if ($nombreLider === '') {
                                                $nombreLider = $lider->email;
                                            }
                                            $puedeMostrarse = ((int) $semillero->leader_id === (int) $lider->id)
                                                || !in_array($lider->id, $lideresYaVinculadosIds ?? [], true);
                                        @endphp
                                        @if($puedeMostrarse)
                                        <option value="{{ $lider->id }}" {{ (string) old('lider_id', $semillero->leader_id) === (string) $lider->id ? 'selected' : '' }}>
                                            {{ $nombreLider }} ({{ $lider->email }})
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
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">No se encontraron semilleros.</td>
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
