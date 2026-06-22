<x-app-layout>
    <x-slot name="header">Panel del Investigador</x-slot>

    @php
        $userId = \Illuminate\Support\Facades\Auth::id();
        /** @var \App\Models\User $user */
        $user   = \Illuminate\Support\Facades\Auth::user();

        $pendiente   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'pendiente')->count();
        $enRevision  = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'en_revision')->count();
        $aprobado    = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'aprobado')->count();
        $rechazado   = \App\Models\GroupProduct::where('author_id', $userId)->where('estado_revision', 'rechazado')->count();
        $totalProds  = $pendiente + $enRevision + $aprobado + $rechazado;

        // Proyectos del investigador
        $proyectosIds = \App\Models\Project::where('project_creator_id', $userId)->pluck('id');
        $proyectos    = $proyectosIds->count();

        // Producción por año (últimos 6 años)
        $porAnio = \App\Models\GroupProduct::where('author_id', $userId)
            ->whereNotNull('anio_publicacion')
            ->selectRaw('anio_publicacion as anio, count(*) as total')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion')
            ->get();

        // Tendencia aprobados por mes (últimos 12 meses)
        $tendenciaInv = \App\Models\GroupProduct::where('author_id', $userId)
            ->where('estado_revision', 'aprobado')
            ->where('updated_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as mes, count(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Últimos productos del investigador
        $misUltimos = \App\Models\GroupProduct::with(['product'])
            ->where('author_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        // Productos pendientes para bandeja de semilleros
        $bandejaCount = \App\Models\Product::whereIn('project_id', $proyectosIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)
            ->whereDoesntHave('groupProducts')
            ->count();
    @endphp

    <div class="space-y-6">

        {{-- Banner de bienvenida --}}
        <div class="relative bg-gradient-to-br from-[#0a1628] to-[#1a3a1a] rounded-2xl overflow-hidden p-6 text-white shadow-lg">
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#39A900]/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-1/3 w-40 h-40 bg-[#39A900]/5 rounded-full translate-y-1/2"></div>
            <div class="relative flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#39A900] flex items-center justify-center shrink-0 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold tracking-widest text-[#39A900] uppercase mb-0.5">Investigador Asociado</p>
                    <h2 class="text-xl font-bold">Bienvenido, {{ $user->person?->primer_nombre ?? $user->email }}</h2>
                    <p class="text-sm text-white/60 mt-1">Sistema de Gestión Documental — SENA</p>
                </div>
                @if($bandejaCount > 0)
                <a href="{{ route('investigador.productos.bandeja') }}"
                   class="shrink-0 flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.222a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162"/></svg>
                    Bandeja ({{ $bandejaCount }})
                </a>
                @endif
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
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-amber-400 hover:shadow-md transition-all">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                </div>
                <p class="text-2xl font-bold text-amber-500">{{ $pendiente }}</p>
                <p class="text-xs text-slate-500 mt-1">Pendientes</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'en_revision']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400 hover:shadow-md transition-all">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                </div>
                <p class="text-2xl font-bold text-blue-500">{{ $enRevision }}</p>
                <p class="text-xs text-slate-500 mt-1">En revisión</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'aprobado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-green-400 hover:shadow-md transition-all">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ $aprobado }}</p>
                <p class="text-xs text-slate-500 mt-1">Aprobados</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:border-red-400 hover:shadow-md transition-all">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                </div>
                <p class="text-2xl font-bold text-red-500">{{ $rechazado }}</p>
                <p class="text-xs text-slate-500 mt-1">Rechazados</p>
            </a>
        </div>

        {{-- FILA DE GRÁFICAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Donut: mis productos por estado --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Mis Productos por Estado</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Distribución actual de tu producción</p>
                </div>
                <div class="p-5">
                    @if($totalProds > 0)
                        <div class="flex flex-col items-center gap-4">
                            <div class="relative" style="width:160px;height:160px;flex-shrink:0;">
                                <canvas id="chartEstadosInv"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-2xl font-bold text-slate-900">{{ $totalProds }}</span>
                                    <span class="text-xs text-slate-400">total</span>
                                </div>
                            </div>
                            <div class="w-full space-y-2 text-xs">
                                <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-green-500"></span> Aprobados</span><strong class="text-slate-800">{{ $aprobado }}</strong></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Pendientes</span><strong class="text-slate-800">{{ $pendiente }}</strong></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-blue-400"></span> En revisión</span><strong class="text-slate-800">{{ $enRevision }}</strong></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-slate-600"><span class="w-3 h-3 rounded-full bg-red-400"></span> Rechazados</span><strong class="text-slate-800">{{ $rechazado }}</strong></div>
                            </div>
                            {{-- Tasa aprobación --}}
                            @if($totalProds > 0)
                            @php $tasaInv = round(($aprobado / $totalProds) * 100); @endphp
                            <div class="w-full border-t border-slate-100 pt-3 mt-1">
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-slate-500 font-medium">Tasa de aprobación</span>
                                    <span class="font-bold text-[#39A900]">{{ $tasaInv }}%</span>
                                </div>
                                <div class="bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-[#39A900] to-emerald-400" style="width: <?= $tasaInv ?>%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"/></svg>
                            Aún no tienes productos registrados.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Barras: producción por año --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Producción por Año</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Mis productos por año de publicación</p>
                </div>
                <div class="p-5">
                    @if($porAnio->isNotEmpty())
                        <canvas id="chartAnioInv" height="195"></canvas>
                    @else
                        <div class="py-10 text-center text-slate-400 text-sm">Sin datos de año registrados.</div>
                    @endif
                </div>
            </div>

            {{-- Línea: tendencia de aprobados por mes --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Tendencia de Aprobaciones</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Aprobados mes a mes (últimos 12 meses)</p>
                </div>
                <div class="p-5">
                    @if($tendenciaInv->isNotEmpty())
                        <canvas id="chartTendenciaInv" height="195"></canvas>
                    @else
                        <div class="py-10 text-center text-slate-400 text-sm">Sin aprobaciones recientes.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- FILA: Últimos productos + Acciones rápidas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Tabla últimos productos --}}
            <div class="lg:col-span-2 sgd-table-card bg-white overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Mis Últimos Productos</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Actividad reciente en tus productos</p>
                    </div>
                    <a href="{{ route('investigador.productos.index') }}"
                       class="sgd-btn-primary px-3 py-1.5 rounded-lg text-xs font-medium">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Producto</th>
                                <th class="text-left">Año</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($misUltimos as $gp)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 max-w-[200px] truncate">
                                    {{ $gp->titulo ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">{{ $gp->anio_publicacion ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php $est = $gp->estado_revision->value ?? 'pendiente'; @endphp
                                    @if($est === 'aprobado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aprobado</span>
                                    @elseif($est === 'rechazado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Rechazado</span>
                                    @elseif($est === 'en_revision')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>En revisión</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-sm">No tienes productos registrados aún.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Acciones rápidas + alertas --}}
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Acciones rápidas</h3>
                    <div class="space-y-2">
                        <a href="{{ route('investigador.productos.create') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Registrar nuevo producto
                        </a>
                        <a href="{{ route('investigador.proyectos.create') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Crear nuevo proyecto
                        </a>
                        <a href="{{ route('investigador.reportes.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-[#f0fdf4] hover:border-[#39A900]/30 text-slate-700 text-sm font-semibold transition-all">
                            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/></svg>
                            Ver mis reportes
                        </a>
                        <a href="{{ route('investigador.estados.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-[#f0fdf4] hover:border-[#39A900]/30 text-slate-700 text-sm font-semibold transition-all">
                            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664"/></svg>
                            Seguimiento
                        </a>
                    </div>
                </div>

                @if($rechazado > 0)
                <div class="bg-red-50 rounded-xl border border-red-200 p-5">
                    <h3 class="text-sm font-semibold text-red-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                        Atención requerida
                    </h3>
                    <p class="text-sm text-slate-600">Tienes <strong class="text-red-600">{{ $rechazado }}</strong> producto(s) rechazado(s).</p>
                    <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
                       class="mt-3 inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-800 font-medium">Ver observaciones →</a>
                </div>
                @elseif($pendiente > 0)
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                    <h3 class="text-sm font-semibold text-amber-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        En espera
                    </h3>
                    <p class="text-sm text-slate-600">Tienes <strong class="text-amber-600">{{ $pendiente }}</strong> producto(s) pendientes de revisión.</p>
                </div>
                @else
                <div class="bg-green-50 rounded-xl border border-green-200 p-5">
                    <h3 class="text-sm font-semibold text-green-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        ¡Todo en orden!
                    </h3>
                    <p class="text-sm text-slate-600">Todos tus productos están al día. ¡Sigue registrando!</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Datos PHP para Chart.js --}}
    <script id="inv-data" type="application/json">
    {
        "aprobado":   {{ $aprobado }},
        "pendiente":  {{ $pendiente }},
        "enRevision": {{ $enRevision }},
        "rechazado":  {{ $rechazado }},
        "total":      {{ $totalProds }},
        "anios":        @json($porAnio->pluck('anio')),
        "aniosTot":     @json($porAnio->pluck('total')),
        "tendenciaMes": @json($tendenciaInv->pluck('mes')),
        "tendenciaTot": @json($tendenciaInv->pluck('total'))
    }
    </script>

    {{-- Chart.js --}}
    <script>
    (function () {
        const d      = JSON.parse(document.getElementById('inv-data').textContent);
        const verde  = '#39A900';
        const verdeT = '#39A90022';
        const amber  = '#f59e0b';
        const blue   = '#60a5fa';
        const red    = '#f87171';

        // Donut: estados
        if (d.total > 0) {
            const ctxDona = document.getElementById('chartEstadosInv');
            if (ctxDona) {
                new Chart(ctxDona, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aprobados', 'Pendientes', 'En revisión', 'Rechazados'],
                        datasets: [{
                            data: [d.aprobado, d.pendiente, d.enRevision, d.rechazado],
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
        }

        // Barras: producción por año
        if (d.anios.length > 0) {
            const ctxAnio = document.getElementById('chartAnioInv');
            if (ctxAnio) {
                new Chart(ctxAnio, {
                    type: 'bar',
                    data: {
                        labels: d.anios,
                        datasets: [{
                            label: 'Productos',
                            data: d.aniosTot,
                            backgroundColor: verdeT,
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
        }

        // Línea: tendencia aprobados por mes
        if (d.tendenciaMes.length > 0) {
            const ctxTend = document.getElementById('chartTendenciaInv');
            if (ctxTend) {
                new Chart(ctxTend, {
                    type: 'line',
                    data: {
                        labels: d.tendenciaMes,
                        datasets: [{
                            label: 'Aprobados',
                            data: d.tendenciaTot,
                            borderColor: verde,
                            backgroundColor: verdeT,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: verde,
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ` ${ctx.raw} aprobado(s)` } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: '#94a3b8', font: { size: 11 } },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { color: '#94a3b8', font: { size: 10 }, maxRotation: 45 },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        }
    })();
    </script>
</x-app-layout>
