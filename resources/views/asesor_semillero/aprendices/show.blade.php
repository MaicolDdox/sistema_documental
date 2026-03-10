@extends('asesor_semillero.layout')

@section('title', 'Detalle del Aprendiz')
@section('header', 'Detalle del Aprendiz')

@section('header-actions')
    @can('aprendices.editar')
        <a href="{{ route('asesor.aprendices.edit', $aprendiz->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
           style="background:#39A900">
            Editar
        </a>
    @endcan
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        {{-- Avatar + nombre --}}
        <div class="flex items-center gap-4 mb-6 pb-5 border-b border-slate-100">
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0" style="background:#0a1628">
                {{ strtoupper(substr($aprendiz->person?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($aprendiz->person?->primer_apellido ?? 'P', 0, 1)) }}
            </div>
            <div>
                <h2 class="font-outfit font-bold text-xl text-slate-900">
                    {{ $aprendiz->person?->primer_nombre }}
                    {{ $aprendiz->person?->segundo_nombre }}
                    {{ $aprendiz->person?->primer_apellido }}
                    {{ $aprendiz->person?->segundo_apellido }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>Inactivo
                    </span>
                    <span class="text-xs text-slate-400">{{ $aprendiz->person?->entityPosition?->nombre ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Datos personales --}}
        <div class="mb-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Documento</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-slate-400">Tipo</p>
                    <p class="text-sm font-medium text-slate-700 capitalize">{{ str_replace('_', ' ', $aprendiz->tipo_documento?->value ?? '—') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Número</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->numero_documento }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Género</p>
                    <p class="text-sm font-medium text-slate-700 capitalize">{{ $aprendiz->person?->genero ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">EPS</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->person?->eps ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="mb-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Contacto</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-slate-400">Celular</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->person?->celular ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Teléfono fijo</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->person?->telefono ?? '—' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-slate-400">Correo institucional</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->person?->email_institucional ?? $aprendiz->email }}</p>
                </div>
            </div>
        </div>

        {{-- Académico --}}
        <div class="mb-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Formación</h3>
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <p class="text-xs text-slate-400">Programa de formación</p>
                    <p class="text-sm font-medium text-slate-700">
                        {{ $aprendiz->person?->trainingProgram?->nombre ?? '—' }}
                        @if($aprendiz->person?->trainingProgram?->trainingProgramType)
                            <span class="text-xs text-slate-400">({{ $aprendiz->person->trainingProgram->trainingProgramType->nombre }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Tipo de vinculación</p>
                    <p class="text-sm font-medium text-slate-700">{{ $aprendiz->person?->linkageType?->nombre ?? '—' }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('asesor.aprendices.index') }}"
           class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Volver a la lista
        </a>
    </div>
</div>
@endsection
