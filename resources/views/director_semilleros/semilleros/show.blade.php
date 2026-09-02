@extends('layouts.sgd')

@section('title', 'Detalle de Semillero')
@section('header', 'Modo Supervisión: ' . $semillero->nombre)

@section('content')
<div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <a href="{{ route('dir-sem.semilleros.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Volver a la lista
    </a>
    
    <div class="flex items-center gap-3">
        <!-- Reasignar Líder Button -->
        @can('semilleros.reasignar_lider')
        <button x-data @click="$dispatch('open-modal', 'modal-reasignar')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            Reasignar Líder
        </button>
        @endcan
        
        <!-- Estado Badge -->
        @if($semillero->estado === \App\Enums\EstadoEnum::Activo)
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                <div class="w-2 h-2 rounded-full bg-green-500"></div> Activo
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                <div class="w-2 h-2 rounded-full bg-slate-400"></div> Inactivo
            </span>
        @endif
    </div>
</div>

<!-- Tarjetas Resumen -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-center">
        <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Líder Actual</p>
        <p class="text-base font-bold text-slate-900 mt-1 truncate" title="{{ $semillero->leader->person->primer_nombre ?? '' }} {{ $semillero->leader->person->primer_apellido ?? '' }}">
            {{ $semillero->leader->person->primer_nombre ?? '' }} {{ $semillero->leader->person->primer_apellido ?? '' }}
        </p>
        <p class="text-sm text-slate-500 truncate">{{ $semillero->leader->email ?? '' }}</p>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-center">
        <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Integrantes</p>
        <p class="text-3xl font-bold text-[#39A900] mt-1">{{ $semillero->integrantes_count }}</p>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-center">
        <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Proyectos Vinculados</p>
        <p class="text-3xl font-bold text-[#39A900] mt-1">{{ $semillero->proyectos_count }}</p>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-center">
        <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Código</p>
        <p class="text-3xl font-bold text-[#39A900] mt-1">{{ $semillero->codigo ?? '—' }}</p>
    </div>
</div>

<!-- Secciones apiladas (página con scroll, sin tabs) -->
<div class="space-y-6">

    @can('semilleros.ver_integrantes')
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Integrantes</h3>
        </div>
        <div class="p-6">
            @include('director_semilleros.semilleros.integrantes', ['semillero' => $semillero])
        </div>
    </div>
    @endcan

    @can('semilleros.ver_proyectos')
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Proyectos</h3>
            <p class="text-xs text-slate-500 mt-0.5">Busca un proyecto para ver sus integrantes, co-investigadores, evidencias y producto final.</p>
        </div>
        <div class="p-6">
            @include('director_semilleros.semilleros.proyectos', ['semillero' => $semillero])
        </div>
    </div>
    @endcan

    @can('documentos.listar')
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Documentos del Semillero</h3>
        </div>
        <div class="p-6">
            @include('director_semilleros.semilleros.documentos', ['semillero' => $semillero, 'documentos' => $documentos])
        </div>
    </div>
    @endcan

    @canany(['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado'])
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Reportes</h3>
        </div>
        <div class="p-6">
            @include('director_semilleros.semilleros.reportes', ['semillero' => $semillero])
        </div>
    </div>
    @endcanany

</div>

<!-- Modal Reasignar Líder -->
@can('semilleros.reasignar_lider')
<div x-data="{ show: false }" 
     @open-modal.window="if ($event.detail === 'modal-reasignar') show = true"
     @close-modal.window="show = false"
     x-show="show" x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center">
     
    <!-- Backdrop -->
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50" @click="show = false"></div>
    
    <!-- Modal -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 z-10 overflow-hidden">
         
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900 font-heading">Reasignar Líder de Semillero</h3>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="{{ route('dir-sem.semilleros.reasignar-lider', $semillero) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <p class="text-sm text-slate-600 mb-4">Selecciona el nuevo líder para el semillero <span class="font-semibold text-slate-800">{{ $semillero->nombre }}</span>. El usuario debe tener el rol de Líder de Semillero.</p>
                
                <label for="nuevo_lider_id" class="block text-sm font-medium text-slate-700 mb-1.5">Nuevo Líder <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="nuevo_lider_id" id="nuevo_lider_id" required
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10">
                        <option value="">Buscar y seleccionar...</option>
                        @php
                            $moduloLideres = \App\Models\User::role('lider_semillero')
                                ->where('training_center_id', Auth::user()->training_center_id)
                                ->where('id', '!=', $semillero->leader_id)
                                ->active()
                                ->get();
                        @endphp
                        @foreach($moduloLideres as $lider)
                            <option value="{{ $lider->id }}">
                                {{ $lider->person->primer_nombre ?? '' }} {{ $lider->person->primer_apellido ?? '' }} ({{ $lider->email }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="show = false" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all">
                    Reasignar Líder
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
