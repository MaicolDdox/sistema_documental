<x-app-layout>
    <x-slot name="header">Dashboard — Grupo de Investigación</x-slot>

    @php
        $grupoId = \App\Models\ResearchGroupUser::where('user_id', auth()->id())
            ->where('rol', \App\Enums\RolGrupoEnum::Director)
            ->value('research_group_id');

        $grupo   = $grupoId ? \App\Models\ResearchGroup::find($grupoId) : null;
        $userIds = $grupoId
            ? \App\Models\ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id')
            : collect();

        $totalInvestigadores = $userIds->count();
        $productosPendientes = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Pendiente)->count();
        $productosAprobados  = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)->count();
        $productosEnRevision = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::EnRevision)->count();
        $productosRechazados = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Rechazado)->count();
        $productosTotal      = \App\Models\GroupProduct::whereIn('author_id', $userIds)->count();

        $ultimosProductos = \App\Models\GroupProduct::with(['author.person', 'product'])
            ->whereIn('author_id', $userIds)
            ->latest()->take(6)->get();

        // Producción por mes (últimos 12 meses)
        $produccionMensual = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $meses = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $meses[$key] = $produccionMensual[$key] ?? 0;
        }
    @endphp

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ $grupo?->nombre ?? 'Grupo de Investigación' }} — Panel del Director
        </p>
    </div>

    {{-- 4 tarjetas de resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Investigadores</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $totalInvestigadores }}</p>
            <p class="text-xs text-slate-400 mt-1">en mi grupo</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total productos</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $productosTotal }}</p>
            <p class="text-xs text-slate-400 mt-1">registrados</p>
        </div>

        <div class="bg-amber-50 rounded-xl border border-amber-100 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Pendientes</p>
            </div>
            <p class="text-3xl font-bold text-amber-700">{{ $productosPendientes }}</p>
            <a href="{{ route('director.productos.index', ['estado_revision' => 'pendiente']) }}"
               class="text-xs text-amber-600 mt-1 hover:underline block">Ver pendientes →</a>
        </div>

        <div class="bg-green-50 rounded-xl border border-green-100 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Aprobados</p>
            </div>
            <p class="text-3xl font-bold text-green-700">{{ $productosAprobados }}</p>
            <p class="text-xs text-green-600 mt-1">productos validados</p>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Gráfica dona: estados de productos --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Distribución de Productos</h2>
                <p class="text-xs text-slate-400 mt-0.5">Por estado de revisión</p>
            </div>
            <div class="p-5 flex items-center justify-center">
                @if($productosTotal > 0)
                    <div class="relative" style="width:220px;height:220px;">
                        <canvas id="chartEstados"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-bold text-slate-900">{{ $productosTotal }}</span>
                            <span class="text-xs text-slate-400">productos</span>
                        </div>
                    </div>
                @else
                    <div class="py-10 text-center text-slate-400 text-sm">Sin productos registrados aún.</div>
                @endif
            </div>
            @if($productosTotal > 0)
            <div class="px-5 pb-5 grid grid-cols-2 gap-2">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-green-500 shrink-0"></span> Aprobados <strong class="ml-auto">{{ $productosAprobados }}</strong>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-amber-400 shrink-0"></span> Pendientes <strong class="ml-auto">{{ $productosPendientes }}</strong>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-blue-400 shrink-0"></span> En revisión <strong class="ml-auto">{{ $productosEnRevision }}</strong>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-red-400 shrink-0"></span> Rechazados <strong class="ml-auto">{{ $productosRechazados }}</strong>
                </div>
            </div>
            @endif
        </div>

        {{-- Gráfica barras: producción mensual --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-sm font-semibold text-slate-900">Producción Mensual</h2>
                <p class="text-xs text-slate-400 mt-0.5">Últimos 12 meses</p>
            </div>
            <div class="p-5">
                <canvas id="chartMensual" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Últimos productos --}}
        <div class="lg:col-span-2">
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Últimos Productos</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Productos de investigadores del grupo</p>
                    </div>
                    <a href="{{ route('director.productos.index') }}"
                       class="sgd-btn-primary px-4 py-2 rounded-xl text-sm font-medium">
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
                                <th class="text-left">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosProductos as $gp)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 max-w-[200px] truncate">
                                    {{ $gp->product?->titulo ?? $gp->titulo ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $gp->author?->person?->primer_nombre ?? $gp->author?->email ?? '—' }}
                                    {{ $gp->author?->person?->primer_apellido ?? '' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php $estado = $gp->estado_revision->value ?? 'pendiente'; @endphp
                                    @if($estado === 'aprobado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aprobado
                                        </span>
                                    @elseif($estado === 'rechazado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Rechazado
                                        </span>
                                    @elseif($estado === 'en_revision')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>En revisión
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('director.productos.show', $gp) }}"
                                       class="text-[#39A900] hover:underline text-xs font-medium">Revisar</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    No hay productos registrados en el grupo aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Acciones rápidas + info grupo --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h2 class="text-base font-semibold text-slate-900">Acciones Rápidas</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('director.investigadores.create') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                        </svg>
                        Nuevo Investigador
                    </a>
                    <a href="{{ route('director.productos.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        Revisar Productos
                        @if($productosPendientes > 0)
                            <span class="ml-auto bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $productosPendientes }}</span>
                        @endif
                    </a>
                    <a href="{{ route('director.documentos.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/>
                        </svg>
                        Documentos del Grupo
                    </a>
                    <a href="{{ route('director.reportes.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                        Reportes
                    </a>
                </div>
            </div>

            @if($grupo)
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Mi Grupo</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nombre</span>
                        <span class="font-medium text-slate-800 text-right max-w-[140px]">{{ $grupo->nombre }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Código</span>
                        <span class="font-mono text-slate-700">{{ $grupo->codigo }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Estado</span>
                        <span class="inline-flex items-center gap-1 text-green-700 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ ucfirst($grupo->estado->value ?? 'activo') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        // Paleta
        const verde    = '#39A900';
        const verdeClr = '#39A90033';
        const amber    = '#f59e0b';
        const blue     = '#60a5fa';
        const red      = '#f87171';
        const slate    = '#94a3b8';

        // --- Gráfica Dona: estados ---
        @if($productosTotal > 0)
        const ctxDona = document.getElementById('chartEstados');
        if (ctxDona) {
            new Chart(ctxDona, {
                type: 'doughnut',
                data: {
                    labels: ['Aprobados', 'Pendientes', 'En revisión', 'Rechazados'],
                    datasets: [{
                        data: [
                            {{ $productosAprobados }},
                            {{ $productosPendientes }},
                            {{ $productosEnRevision }},
                            {{ $productosRechazados }},
                        ],
                        backgroundColor: [verde, amber, blue, red],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} productos`
                            }
                        }
                    }
                }
            });
        }
        @endif

        // --- Gráfica Barras: producción mensual ---
        const ctxBar = document.getElementById('chartMensual');
        if (ctxBar) {
            const labels = @json($meses->keys()->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M y')));
            const values = @json($meses->values());

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Productos',
                        data: values,
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
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.raw} producto(s)`
                            }
                        }
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
    })();
    </script>
</x-app-layout>
