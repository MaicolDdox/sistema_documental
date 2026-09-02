@extends('layouts.sgd')

@section('title', $proyecto->nombre)
@section('header', '')

@section('content')
<div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <a href="{{ route('lider-sem.proyectos') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Volver a proyectos
    </a>
    <a href="{{ route('lider-sem.proyectos.edit', $proyecto) }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center gap-2 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
        Editar
    </a>
</div>

<div class="space-y-6">

    {{-- Card 1: Descripción del proyecto --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">{{ $proyecto->nombre }}</h3>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $proyecto->estado?->value === 'activo' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $proyecto->estado?->value === 'activo' ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-700">{{ $proyecto->descripcion ?? 'Sin descripción registrada.' }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Línea de Investigación</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->researchLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Línea Tecnológica</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->technologicalLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Área Temática</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->thematicArea?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Modalidad</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->projectModality?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Tipo de Investigación</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->investigationType?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide">Fechas</p>
                    <p class="text-sm text-slate-800 mt-0.5">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? 'N/A' }} → {{ $proyecto->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Integrantes del proyecto --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Integrantes del Proyecto</h3>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Líder de Proyecto</p>
                @if($proyecto->liderProyecto)
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2.5 w-fit">
                    <span class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center text-xs font-bold text-[#39A900]">{{ $proyecto->liderProyecto->initials() }}</span>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $proyecto->liderProyecto->person?->nombre_completo ?? $proyecto->liderProyecto->email }}</p>
                        <p class="text-xs text-slate-500">{{ $proyecto->liderProyecto->email }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-400">Sin líder de proyecto asignado.</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Aprendices Registrados ({{ $proyecto->learners->count() }})</p>
                <div class="space-y-1.5">
                    @forelse($proyecto->learners as $aprendiz)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                        <span class="font-medium text-slate-800">{{ $aprendiz->nombre_completo }}</span>
                        <span class="text-slate-500">Doc: {{ $aprendiz->numero_documento }} @if($aprendiz->ficha) · Ficha: {{ $aprendiz->ficha }} @endif</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">Sin aprendices registrados.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Co-investigadores ({{ $proyecto->authors->count() }})</p>
                <div class="space-y-1.5">
                    @forelse($proyecto->authors as $coinvestigador)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                        <span class="font-medium text-slate-800">{{ $coinvestigador->person?->nombre_completo ?? $coinvestigador->email }}</span>
                        <span class="text-slate-500">{{ $coinvestigador->email }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">Sin co-investigador vinculado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Avances del proyecto --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Avances del Proyecto</h3>
            <p class="text-xs text-slate-500 mt-0.5">Documentación de avance cargada por el Líder de Proyecto.</p>
        </div>
        <div class="p-6">
            <div class="space-y-2">
                @forelse($proyecto->evidenciasDesarrollo as $avance)
                <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-100 rounded-lg px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $avance->nombre }}</p>
                        @if($avance->descripcion)
                        <p class="text-xs text-slate-500 truncate">{{ $avance->descripcion }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-0.5">{{ $avance->created_at->format('d/m/Y') }}</p>
                    </div>
                    @if($avance->archivo)
                    <a href="{{ route('lider-sem.evidencias.descargar', $avance) }}" class="text-[#39A900] hover:underline text-xs font-medium shrink-0">Descargar</a>
                    @endif
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">Aún no hay avances cargados para este proyecto.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
