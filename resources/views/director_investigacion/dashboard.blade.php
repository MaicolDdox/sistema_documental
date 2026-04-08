<x-app-layout>
    <x-slot name="header">Dashboard — Grupo de Investigación</x-slot>

    @php
        $grupoId = \App\Models\ResearchGroupUser::where('user_id', auth()->id())
            ->where('rol', \App\Enums\RolGrupoEnum::Director)
            ->value('research_group_id');

        $grupo   = $grupoId ? \App\Models\ResearchGroup::with('trainingCenter')->find($grupoId) : null;
        $pivots  = $grupoId
            ? \App\Models\ResearchGroupUser::with('user.person')->where('research_group_id', $grupoId)->get()
            : collect();
        $userIds = $pivots->pluck('user_id');

        $totalInvestigadores = $pivots->filter(fn($p) => $p->rol !== \App\Enums\RolGrupoEnum::Director)->count();

        $productosPendientes = \App\Models\GroupProduct::whereIn('author_id', $userIds)->where('estado_revision', \App\Enums\EstadoRevisionEnum::Pendiente)->count();
        $productosEnRevision = \App\Models\GroupProduct::whereIn('author_id', $userIds)->where('estado_revision', \App\Enums\EstadoRevisionEnum::EnRevision)->count();
        $productosAprobados  = \App\Models\GroupProduct::whereIn('author_id', $userIds)->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)->count();
        $productosRechazados = \App\Models\GroupProduct::whereIn('author_id', $userIds)->where('estado_revision', \App\Enums\EstadoRevisionEnum::Rechazado)->count();
        $productosTotal      = $productosPendientes + $productosEnRevision + $productosAprobados + $productosRechazados;

        // Últimos productos para la tabla
        $ultimosProductos = \App\Models\GroupProduct::with(['author.person', 'product'])
            ->whereIn('author_id', $userIds)
            ->latest()->take(6)->get();

        // Productos por investigador (para gráfica de barras)
        $prodPorInvestigador = \App\Models\GroupProduct::selectRaw('author_id, count(*) as total')
            ->whereIn('author_id', $userIds)
            ->groupBy('author_id')
            ->get()
            ->map(function ($row) use ($pivots) {
                $pivot = $pivots->firstWhere('user_id', $row->author_id);
                $nombre = $pivot?->user?->person?->primer_nombre
                    ? trim($pivot->user->person->primer_nombre . ' ' . $pivot->user->person->primer_apellido)
                    : ($pivot?->user?->email ?? 'Desconocido');
                return ['nombre' => $nombre, 'total' => $row->total];
            });

        // Producción por año (todos los investigadores del grupo)
        $prodPorAnio = \App\Models\GroupProduct::selectRaw('anio_publicacion as anio, count(*) as total')
            ->whereIn('author_id', $userIds)
            ->whereNotNull('anio_publicacion')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion')
            ->get();

        // Aprobados por mes (últimos 12 meses) para gráfica de línea de tendencia
        $tendencia = \App\Models\GroupProduct::selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as mes, count(*) as total")
            ->whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)
            ->where('updated_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        // Pre-calcular investigadores (sin director) para no usar la clase Enum en el HTML
        $noDirectores = $pivots->filter(fn($p) => $p->rol !== \App\Enums\RolGrupoEnum::Director);
    @endphp

    {{-- Encabezado / hero --}}
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.2em] text-[#39A900] uppercase mb-1">Panel del Director</p>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                Dashboard
                @if($grupo)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ $grupo->codigo }}
                    </span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $grupo?->nombre ?? 'Grupo de Investigación' }}
                @if($grupo?->trainingCenter)
                    · <span class="text-[#39A900] font-medium">{{ $grupo->trainingCenter->nombre }}</span>
                @endif
            </p>
        </div>
        @if($grupo)
        <div class="flex items-center gap-4 bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-[#f0fdf4] flex items-center justify-center">
                <svg class="w-6 h-6 text-[#39A900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h10.5M8.25 19.5h7.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Mi Grupo</p>
                <p class="text-sm font-medium text-slate-900 leading-tight">{{ $grupo->nombre }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ ucfirst($grupo->estado->value ?? 'activo') }}
                </p>
            </div>
        </div>
        @endif
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm relative overflow-hidden col-span-2 sm:col-span-1">
            <div class="absolute -top-5 -right-3 w-16 h-16 bg-[#f0fdf4] rounded-full opacity-70"></div>
            <div class="relative">
                <div class="w-8 h-8 rounded-lg bg-[#f0fdf4] flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372A9.337 9.337 0 0021 18M4.5 19.5A9.375 9.375 0 019 15.75M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 10.5a3.75 3.75 0 10-7.5 0 3.75 3.75 0 007.5 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900">{{ $totalInvestigadores }}</p>
                <p class="text-xs text-slate-400 mt-1">Investigadores</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5M4.5 9h15M9 13.5h6m-8.25 4.5h10.5"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $productosTotal }}</p>
            <p class="text-xs text-slate-400 mt-1">Productos totales</p>
        </div>
        <div class="bg-amber-50/80 rounded-xl border border-amber-100 p-5 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-amber-700">{{ $productosPendientes }}</p>
            <a href="{{ route('director.productos.index', ['estado_revision' => 'pendiente']) }}"
               class="text-xs font-semibold text-amber-700 mt-1 hover:underline inline-flex items-center gap-1">
                Pendientes <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="bg-blue-50/70 rounded-xl border border-blue-100 p-5 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-blue-600">{{ $productosEnRevision }}</p>
            <p class="text-xs text-blue-600 mt-1">En revisión</p>
        </div>
        <div class="bg-green-50/70 rounded-xl border border-green-100 p-5 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-green-700">{{ $productosAprobados }}</p>
            <p class="text-xs text-green-700 mt-1">Aprobados</p>
        </div>
    </div>

    {{-- FILA 1: Gráficas principales --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Donut: distribución de estados --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Distribución por Estado</h2>
                <p class="text-xs text-slate-400 mt-0.5">Avance de revisión del grupo</p>
            </div>
            <div class="p-5 flex flex-col items-center">
                @if($productosTotal > 0)
                    <div class="relative w-44 h-44 mb-4">
                        <canvas id="chartDonutDirector"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-bold text-slate-900">{{ $productosTotal }}</span>
                            <span class="text-xs text-slate-400">total</span>
                        </div>
                    </div>
                    <div class="w-full space-y-2 text-xs">
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>Pendiente</span><span class="font-semibold text-slate-700">{{ $productosPendientes }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>En revisión</span><span class="font-semibold text-slate-700">{{ $productosEnRevision }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>Aprobado</span><span class="font-semibold text-slate-700">{{ $productosAprobados }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Rechazado</span><span class="font-semibold text-slate-700">{{ $productosRechazados }}</span></div>
                    </div>
                @else
                    <div class="py-10 text-center text-slate-400 text-sm">Sin productos aún.</div>
                @endif
            </div>
        </div>

        {{-- Barras: productos por investigador --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Productos por Investigador</h2>
                <p class="text-xs text-slate-400 mt-0.5">Cantidad de productos registrados por cada miembro del grupo</p>
            </div>
            <div class="p-5">
                @if($prodPorInvestigador->isNotEmpty())
                    <canvas id="chartBarInvestigador" height="185"></canvas>
                @else
                    <div class="py-10 text-center text-slate-400 text-sm">No hay datos de producción por investigador aún.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- FILA 2: Producción por año + Tabla últimos productos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Línea: aprobados por mes (tendencia) --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Tendencia Aprobaciones</h2>
                <p class="text-xs text-slate-400 mt-0.5">Productos aprobados mes a mes (últimos 12 meses)</p>
            </div>
            <div class="p-5">
                @if($tendencia->isNotEmpty())
                    <canvas id="chartTendenciaDirector" height="185"></canvas>
                @else
                    <div class="py-10 text-center text-slate-400 text-sm">Sin productos aprobados en los últimos 12 meses.</div>
                @endif
            </div>
        </div>

        {{-- Barras: producción por año --}}
        @if($prodPorAnio->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Producción por Año</h2>
                <p class="text-xs text-slate-400 mt-0.5">Productos del grupo por año de publicación</p>
            </div>
            <div class="p-5">
                <canvas id="chartAnioDirector" height="185"></canvas>
            </div>
        </div>
        @endif

        {{-- Últimos productos --}}
        <div class="{{ $prodPorAnio->isNotEmpty() ? '' : 'lg:col-span-2' }} sgd-table-card bg-white overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Últimos Productos</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Productos recientes del grupo</p>
                </div>
                <a href="{{ route('director.productos.index') }}"
                   class="sgd-btn-primary px-3 py-1.5 rounded-lg text-xs font-medium">
                    Ver todos
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="sgd-table text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Producto</th>
                            <th class="text-left">Investigador</th>
                            <th class="text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimosProductos as $gp)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800 max-w-[160px] truncate">
                                {{ $gp->product?->titulo ?? $gp->titulo ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">
                                {{ $gp->author?->person?->primer_nombre ?? $gp->author?->email ?? '—' }}
                                {{ $gp->author?->person?->primer_apellido ?? '' }}
                            </td>
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
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-sm">No hay productos registrados en el grupo aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FILA 3: Acciones rápidas + Info grupo --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Acciones Rápidas</h2>
            </div>
            <div class="p-4 space-y-2">
                <a href="{{ route('director.investigadores.create') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                    Nuevo Investigador
                </a>
                <a href="{{ route('director.productos.index') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    Revisar Productos
                    @if($productosPendientes > 0)
                        <span class="ml-auto bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $productosPendientes }}</span>
                    @endif
                </a>
                <a href="{{ route('director.macroproyectos.index') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5M3.75 9h16.5M3.75 13.5h16.5M3.75 18h16.5"/></svg>
                    Macroproyectos
                </a>
                <a href="{{ route('director.documentos.index') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/></svg>
                    Documentos del Grupo
                </a>
                <a href="{{ route('director.reportes.index') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Reportes
                </a>
            </div>
        </div>

        @if($grupo)
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Info del Grupo</h3>
            <div class="space-y-2.5 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nombre</span>
                    <span class="font-medium text-slate-800 text-right max-w-[160px] leading-tight">{{ $grupo->nombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Código</span>
                    <span class="font-mono text-slate-700">{{ $grupo->codigo }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sede</span>
                    <span class="text-slate-700 text-right max-w-[130px]">{{ $grupo->trainingCenter?->nombre ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Estado</span>
                    <span class="inline-flex items-center gap-1.5 text-green-700 text-xs font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        {{ ucfirst($grupo->estado->value ?? 'activo') }}
                    </span>
                </div>
            </div>
            {{-- Barra de progreso de aprobación --}}
            @if($productosTotal > 0)
            <div class="border-t border-slate-100 pt-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tasa de aprobación</p>
                @php $tasa = round(($productosAprobados / $productosTotal) * 100); @endphp
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full bg-gradient-to-r from-[#39A900] to-emerald-400 transition-all" style="width: {{ $tasa }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700">{{ $tasa }}%</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Mini tabla de investigadores --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Investigadores</h3>
                <a href="{{ route('director.investigadores.index') }}" class="text-xs text-[#39A900] hover:underline font-medium">Ver todos →</a>
            </div>
            <div class="space-y-2.5">
                @forelse($noDirectores->take(5) as $piv)
                @php $u = $piv->user; @endphp
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold text-[#39A900]">
                            {{ strtoupper(substr($u?->person?->primer_nombre ?? $u?->email ?? 'U', 0, 1)) }}{{ strtoupper(substr($u?->person?->primer_apellido ?? '', 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-800 truncate">
                            {{ $u?->person?->primer_nombre ?? $u?->email ?? '—' }} {{ $u?->person?->primer_apellido ?? '' }}
                        </p>
                        <p class="text-[10px] text-slate-400 truncate">{{ ucfirst(str_replace('_', ' ', $piv->rol->value ?? '')) }}</p>
                    </div>
                    @if(($u?->estado?->value ?? '') === 'activo')
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                    @endif
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">Sin investigadores vinculados aún.</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const verde    = '#39A900';
        const verdeT   = '#39A90022';
        const amber    = '#f59e0b';
        const blue     = '#38bdf8';
        const red      = '#f87171';

        // Donut: distribución de estados
        @if($productosTotal > 0)
        const ctxDonut = document.getElementById('chartDonutDirector');
        if (ctxDonut) {
            new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: ['Pendiente', 'En revisión', 'Aprobado', 'Rechazado'],
                    datasets: [{
                        data: [{{ $productosPendientes }}, {{ $productosEnRevision }}, {{ $productosAprobados }}, {{ $productosRechazados }}],
                        backgroundColor: [amber, blue, verde, red],
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
                    },
                    animation: { animateRotate: true, duration: 800 }
                }
            });
        }
        @endif

        // Barras: productos por investigador
        @if($prodPorInvestigador->isNotEmpty())
        const ctxBar = document.getElementById('chartBarInvestigador');
        if (ctxBar) {
            const nombres = @json($prodPorInvestigador->pluck('nombre'));
            const totales = @json($prodPorInvestigador->pluck('total'));
            // Abreviar nombres largos
            const nombresCortos = nombres.map(n => n.length > 18 ? n.substr(0, 15) + '…' : n);
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: nombresCortos,
                    datasets: [{
                        label: 'Productos',
                        data: totales,
                        backgroundColor: verdeT,
                        borderColor: verde,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.raw} producto(s)`, title: i => nombres[i[0].dataIndex] } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#94a3b8', font: { size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { color: '#64748b', font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        @endif

        // Tendencia: aprobados por mes
        @if($tendencia->isNotEmpty())
        const ctxTend = document.getElementById('chartTendenciaDirector');
        if (ctxTend) {
            new Chart(ctxTend, {
                type: 'line',
                data: {
                    labels: @json($tendencia->pluck('mes')),
                    datasets: [{
                        label: 'Aprobados',
                        data: @json($tendencia->pluck('total')),
                        borderColor: verde,
                        backgroundColor: verdeT,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: verde,
                        fill: true,
                        tension: 0.35,
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
                            ticks: { color: '#94a3b8', font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        @endif

        // Barras: producción por año
        @if($prodPorAnio->isNotEmpty())
        const ctxAnio = document.getElementById('chartAnioDirector');
        if (ctxAnio) {
            new Chart(ctxAnio, {
                type: 'bar',
                data: {
                    labels: @json($prodPorAnio->pluck('anio')),
                    datasets: [{
                        label: 'Productos',
                        data: @json($prodPorAnio->pluck('total')),
                        backgroundColor: '#0ea5e922',
                        borderColor: '#0ea5e9',
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
