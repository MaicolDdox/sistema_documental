@extends('layouts.sgd')

@section('title', 'Líderes de Proyecto')
@section('header', '')

@section('content')
<div>
    <h2 class="text-xl font-bold text-slate-900 mb-1">Líderes de Proyecto</h2>
    <p class="text-sm text-slate-500 mb-4">Usuarios con rol Líder de Proyecto que has creado. La asignación a un proyecto específico se habilita cuando el módulo de proyectos esté disponible.</p>

    @if(session('credenciales'))
    @php $credenciales = session('credenciales'); @endphp
    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm">
        <p class="font-medium text-amber-900 mb-1">Credenciales del nuevo líder de proyecto:</p>
        <p class="text-amber-800"><strong>Correo:</strong> {{ $credenciales['email'] ?? '-' }}</p>
        <p class="text-amber-800"><strong>Contraseña temporal:</strong> <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono">{{ $credenciales['password'] ?? '-' }}</code></p>
        <p class="text-amber-700 text-xs mt-1">Entrégalas al líder de proyecto y pídele que la cambie al primer ingreso.</p>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('lider-sem.lider-proyecto.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar líder de proyecto..."
                   class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            <button type="submit" class="border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
                Buscar
            </button>
        </form>
        <a href="{{ route('lider-sem.lider-proyecto.create') }}"
           class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo Líder de Proyecto
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 font-medium text-slate-500">Nombre</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-500">Documento</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-500">Email</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500">Estado</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-500">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lideresProyecto as $lp)
                    @php
                        $nombreCompleto = $lp->person
                            ? trim(($lp->person->primer_nombre ?? '') . ' ' . ($lp->person->primer_apellido ?? ''))
                            : $lp->email;
                        if ($nombreCompleto === '') { $nombreCompleto = $lp->email; }
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#39A900]/20 text-[#39A900] flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $lp->initials() }}
                                </div>
                                <span class="font-medium text-slate-900">{{ $nombreCompleto }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $lp->numero_documento }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $lp->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($lp->estado === \App\Enums\EstadoEnum::Activo)
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
                            <form method="POST" action="{{ route('lider-sem.lider-proyecto.toggle-estado', $lp) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-all
                                               {{ $lp->estado === \App\Enums\EstadoEnum::Activo
                                                  ? 'border-red-200 text-red-700 hover:bg-red-50'
                                                  : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                                    {{ $lp->estado === \App\Enums\EstadoEnum::Activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500 text-sm">
                            Aún no has creado ningún líder de proyecto.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lideresProyecto->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $lideresProyecto->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
