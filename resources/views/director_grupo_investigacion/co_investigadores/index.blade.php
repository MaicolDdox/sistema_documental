@extends('layouts.sgd')

@section('title', 'Co-investigadores GDI')
@section('header', '')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Co-investigadores GDI</h1>
        <p class="text-sm text-slate-500 mt-0.5">Usuarios con rol Co-investigador GDI de tu grupo de investigación.</p>
    </div>
    @can('usuarios.crear_co_investigador_gdi')
    <a href="{{ route('director-grupo-investigacion.co-investigadores.create') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo Co-investigador
    </a>
    @endcan
</div>

<form method="GET" action="{{ route('director-grupo-investigacion.co-investigadores.index') }}" class="flex gap-2 max-w-md mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar co-investigador..."
           class="sgd-input-focus w-full pl-3 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
    <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">Buscar</button>
</form>

<div class="sgd-table-card bg-white">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Documento</th>
                    <th class="text-left">Email</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coInvestigadores as $ci)
                @php
                    $nombreCompleto = $ci->person
                        ? trim(($ci->person->primer_nombre ?? '').' '.($ci->person->primer_apellido ?? ''))
                        : $ci->email;
                    if ($nombreCompleto === '') { $nombreCompleto = $ci->email; }
                @endphp
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $nombreCompleto }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $ci->numero_documento }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $ci->email }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($ci->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-slate-500">Aún no has registrado co-investigadores GDI.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coInvestigadores->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/30">
        {{ $coInvestigadores->links() }}
    </div>
    @endif
</div>
@endsection
