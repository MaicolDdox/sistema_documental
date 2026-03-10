@extends('asesor_semillero.layout')

@section('title', 'Integrantes del Proyecto')
@section('header', 'Gestionar Integrantes')

@section('content')
<div class="mb-3">
    <a href="{{ route('asesor.proyectos.show', $proyecto->id) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        {{ Str::limit($proyecto->nombre, 50) }}
    </a>
</div>

@if($miembrosSinProyecto->isNotEmpty())
<div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
    ⚠️ <strong>{{ $miembrosSinProyecto->count() }} aprendice(s)</strong> del semillero sin ningún proyecto asignado.
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Autores actuales --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Autores del proyecto ({{ $autoresActuales->count() }})</h3>
        @if($autoresActuales->isEmpty())
            <p class="text-sm text-slate-400">Sin autores vinculados.</p>
        @else
        <div class="space-y-2">
            @foreach($autoresActuales as $autor)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0" style="background:#0a1628">
                        {{ strtoupper(substr($autor->user?->person?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($autor->user?->person?->primer_apellido ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $autor->user?->person?->primer_nombre }} {{ $autor->user?->person?->primer_apellido }}
                            @if($autor->user_id === auth()->id())
                                <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Asesor</span>
                            @endif
                        </p>
                    </div>
                </div>
                @if($autor->user_id !== auth()->id())
                <form method="POST" action="{{ route('asesor.proyectos.desvincular', [$proyecto->id, $autor->user_id]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('¿Desvincular este integrante del proyecto?')"
                            class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                        Desvincular
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Disponibles para vincular --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Miembros disponibles ({{ $disponibles->count() }})</h3>
        @if($disponibles->isEmpty())
            <p class="text-sm text-slate-400">Todos los miembros del semillero ya están en este proyecto.</p>
        @else
        <div class="space-y-2">
            @foreach($disponibles as $miembro)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0 bg-slate-400">
                        {{ strtoupper(substr($miembro->person?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($miembro->person?->primer_apellido ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $miembro->person?->primer_nombre }} {{ $miembro->person?->primer_apellido }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $miembro->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('asesor.proyectos.vincular', $proyecto->id) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $miembro->id }}">
                    <button type="submit"
                            class="text-xs px-2.5 py-1.5 rounded-lg text-white transition-all hover:opacity-90" style="background:#39A900">
                        Vincular
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
