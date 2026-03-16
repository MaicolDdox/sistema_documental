<x-app-layout>
    <x-slot name="header">Panel del Investigador</x-slot>

    {{-- Contadores de estado --}}
    @php
        $userId = auth()->id();
        $pendiente   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'pendiente')->count();
        $enRevision  = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'en_revision')->count();
        $aprobado    = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'aprobado')->count();
        $rechazado   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'rechazado')->count();
        $proyectos   = \App\Models\Project::where('project_creator_id', $userId)->count();
    @endphp

    <div class="space-y-6">

        {{-- Bienvenida --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#f0fdf4] flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Bienvenido, {{ auth()->user()->person?->primer_nombre ?? auth()->user()->email }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Módulo de Investigador Asociado — Grupo GIDESTH</p>
                    <p class="text-xs text-slate-400 mt-1">Registra productos del semillero y gestiona tus proyectos de investigación.</p>
                </div>
            </div>
        </div>

        {{-- Tarjetas de resumen --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            {{-- Proyectos --}}
            <a href="{{ route('investigador.proyectos.index') }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-[#39A900] transition-all group">
                <p class="text-2xl font-bold text-slate-900 group-hover:text-[#39A900] transition-colors">{{ $proyectos }}</p>
                <p class="text-xs text-slate-500 mt-1">Proyectos</p>
            </a>

            {{-- Pendiente --}}
            <a href="{{ route('investigador.estados.index', ['estado' => 'pendiente']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-amber-400 transition-all group">
                <p class="text-2xl font-bold text-amber-500">{{ $pendiente }}</p>
                <p class="text-xs text-slate-500 mt-1">Pendientes</p>
            </a>

            {{-- En revisión --}}
            <a href="{{ route('investigador.estados.index', ['estado' => 'en_revision']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400 transition-all group">
                <p class="text-2xl font-bold text-blue-500">{{ $enRevision }}</p>
                <p class="text-xs text-slate-500 mt-1">En revisión</p>
            </a>

            {{-- Aprobado --}}
            <a href="{{ route('investigador.estados.index', ['estado' => 'aprobado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-green-400 transition-all group">
                <p class="text-2xl font-bold text-green-600">{{ $aprobado }}</p>
                <p class="text-xs text-slate-500 mt-1">Aprobados</p>
            </a>

            {{-- Rechazado --}}
            <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-red-400 transition-all group">
                <p class="text-2xl font-bold text-red-500">{{ $rechazado }}</p>
                <p class="text-xs text-slate-500 mt-1">Rechazados</p>
            </a>
        </div>

        {{-- Acciones rápidas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Acciones rápidas</h3>
                <div class="space-y-2">
                    <a href="{{ route('investigador.productos.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Registrar nuevo producto
                    </a>
                    <a href="{{ route('investigador.proyectos.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Crear nuevo proyecto
                    </a>
                </div>
            </div>

            {{-- Productos rechazados pendientes de corrección --}}
            @if($rechazado > 0)
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h3 class="text-sm font-semibold text-red-700 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    Atención requerida
                </h3>
                <p class="text-sm text-slate-600">Tienes <strong class="text-red-600">{{ $rechazado }}</strong> producto(s) rechazado(s) que requieren corrección.</p>
                <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
                   class="mt-3 inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-800 font-medium">
                    Ver observaciones →
                </a>
            </div>
            @else
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-2">Estado de tus productos</h3>
                <p class="text-sm text-slate-500">Todos tus productos están en orden. ¡Sigue registrando!</p>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
