<x-app-layout>
    <x-slot name="header">Panel del Investigador</x-slot>

    @php
        $userId = auth()->id();
        $pendiente   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'pendiente')->count();
        $enRevision  = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'en_revision')->count();
        $aprobado    = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'aprobado')->count();
        $rechazado   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'rechazado')->count();
        $totalProds  = $pendiente + $enRevision + $aprobado + $rechazado;
        $proyectos   = \App\Models\Project::where('project_creator_id', $userId)->count();

        // Producción por año (últimos 5 años)
        $porAnio = \App\Models\GroupProduct::where('author_id', $userId)
            ->whereNotNull('anio_publicacion')
            ->selectRaw('anio_publicacion as anio, count(*) as total')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion')
            ->get();
    @endphp

    <div class="space-y-6">

        {{-- Bienvenida --}}
        <div class="bg-gradient-to-r from-[#f0fdf4] to-white rounded-xl border border-[#39A900]/20 p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#39A900] flex items-center justify-center flex-shrink-0 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Bienvenido, {{ auth()->user()->person?->primer_nombre ?? auth()->user()->email }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Investigador Asociado — Sistema de Gestión Documental</p>
                    <p class="text-xs text-slate-400 mt-1">Registra productos del semillero y gestiona tus proyectos de investigación.</p>
                </div>
            </div>
        </div>

        {{-- Tarjetas de resumen --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('investigador.proyectos.index') }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-[#39A900] hover:shadow-md transition-all group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-3 group-hover:bg-[#39A900] transition-colors">
                    <svg class="w-4 h-4 text-blue-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-slate-900 group-hover:text-[#39A900] transition-colors">{{ $proyectos }}</p>
                <p class="text-xs text-slate-500 mt-1">Proyectos</p>
            </a>

            <a href="{{ route('investigador.estados.index', ['estado' => 'pendiente']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-amber-400 hover:shadow-md transition-all group">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                </div>
                <p class="text-2xl font-bold text-amber-500">{{ $pendiente }}</p>
                <p class="text-xs text-slate-500 mt-1">Pendientes</p>
            </a>

            <a href="{{ route('investigador.estados.index', ['estado' => 'en_revision']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400 hover:shadow-md transition-all group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                </div>
                <p class="text-2xl font-bold text-blue-500">{{ $enRevision }}</p>
                <p class="text-xs text-slate-500 mt-1">En revisión</p>
            </a>

            <a href="{{ route('investigador.estados.index', ['estado' => 'aprobado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-green-400 hover:shadow-md transition-all group">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ $aprobado }}</p>
                <p class="text-xs text-slate-500 mt-1">Aprobados</p>
            </a>

            <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-red-400 hover:shadow-md transition-all group">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                </div>
                <p class="text-2xl font-bold text-red-500">{{ $rechazado }}</p>
                <p class="text-xs text-slate-500 mt-1">Rechazados</p>
            </a>
        </div>

        {{-- Gráficas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Dona: mis productos por estado --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Mis Productos por Estado</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Distribución actual de tu producción</p>
                </div>
                <div class="p-5">
                    @if($totalProds > 0)
                        <div class="flex items-center gap-6">
                            <div class="relative" style="width:160px;height:160px;flex-shrink:0;">
                                <canvas id="chartEstadosInv"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-xl font-bold text-slate-900">{{ $totalProds }}</span>
                                    <span class="text-xs text-slate-400">total</span>
                                </div>
                            </div>
                            <div class="space-y-2.5 flex-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-green-500"></span> Aprobados</span>
                                    <strong class="text-slate-800">{{ $aprobado }}</strong>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Pendientes</span>
                                    <strong class="text-slate-800">{{ $pendiente }}</strong>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-blue-400"></span> En revisión</span>
                                    <strong class="text-slate-800">{{ $enRevision }}</strong>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-red-400"></span> Rechazados</span>
                                    <strong class="text-slate-800">{{ $rechazado }}</strong>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"/>
                            </svg>
                            Aún no tienes productos registrados.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Barras: mis productos por año --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Producción por Año</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Mis productos clasificados por año de publicación</p>
                </div>
                <div class="p-5">
                    @if($porAnio->isNotEmpty())
                        <canvas id="chartAnioInv" height="180"></canvas>
                    @else
                        <div class="py-8 text-center text-slate-400 text-sm">Sin datos de año disponibles.</div>
                    @endif
                </div>
            </div>
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
                    <a href="{{ route('investigador.reportes.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-[#f0fdf4] hover:border-[#39A900]/30 text-slate-700 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                        Ver mis reportes
                    </a>
                </div>
            </div>

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

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const verde    = '#39A900';
        const verdeClr = '#39A90033';
        const amber    = '#f59e0b';
        const blue     = '#60a5fa';
        const red      = '#f87171';

        @if($totalProds > 0)
        const ctxDona = document.getElementById('chartEstadosInv');
        if (ctxDona) {
            new Chart(ctxDona, {
                type: 'doughnut',
                data: {
                    labels: ['Aprobados', 'Pendientes', 'En revisión', 'Rechazados'],
                    datasets: [{
                        data: [{{ $aprobado }}, {{ $pendiente }}, {{ $enRevision }}, {{ $rechazado }}],
                        backgroundColor: [verde, amber, blue, red],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
                    }
                }
            });
        }
        @endif

        @if($porAnio->isNotEmpty())
        const ctxAnio = document.getElementById('chartAnioInv');
        if (ctxAnio) {
            new Chart(ctxAnio, {
                type: 'bar',
                data: {
                    labels: @json($porAnio->pluck('anio')),
                    datasets: [{
                        label: 'Productos',
                        data: @json($porAnio->pluck('total')),
                        backgroundColor: verdeClr,
                        borderColor: verde,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.raw} producto(s)` } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#94a3b8', font: { size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { color: '#94a3b8', font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        @endif
    })();
    </script>
</x-app-layout>
