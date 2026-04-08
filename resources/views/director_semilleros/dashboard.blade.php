@extends('layouts.sgd')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">SGD — Panel de Director de Semilleros</p>
</div>

{{-- 4 tarjetas de resumen --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Semilleros a mi cargo</p>
        <p class="text-2xl font-bold text-slate-900">{{ $totalSemilleros }}</p>
        <p class="text-xs text-slate-500 mt-1 border-b-2 border-[#39A900] pb-0.5 w-fit">{{ $semillerosActivos }} activos</p>
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Líderes asignados</p>
        <p class="text-2xl font-bold text-slate-900">{{ $totalLideres }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $lideresEsteMes > 0 ? '+' . $lideresEsteMes . ' este mes' : '—' }}</p>
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Integrantes totales</p>
        <p class="text-2xl font-bold text-slate-900">{{ $integrantesTotales }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $integrantesNuevos > 0 ? '+' . $integrantesNuevos . ' nuevos' : '—' }}</p>
    </div>
    <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 mb-1">Asesores vinculados</p>
        <p class="text-2xl font-bold text-slate-900">{{ $asesoresVinculados }}</p>
        <p class="text-xs text-slate-500 mt-1">Activos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Mis Semilleros (tabla) + Líderes (lista) --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h2 class="text-base font-semibold text-slate-900">Resumen gráfico del módulo</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Vista rápida de capacidad y carga actual</p>
                </div>
                <div class="p-4">
                    <div class="h-64">
                        <canvas id="chart-dir-sem-resumen"></canvas>
                    </div>
                </div>
            </div>
            <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h2 class="text-base font-semibold text-slate-900">Estado de semilleros</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Distribución activos/inactivos</p>
                </div>
                <div class="p-4">
                    <div class="h-64">
                        <canvas id="chart-dir-sem-estado"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="sgd-table-card bg-white overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Mis Semilleros</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Estado actual</p>
                </div>
                <a href="{{ route('dir-sem.semilleros.index') }}" class="sgd-btn-primary px-4 py-2 rounded-xl text-sm font-medium">
                    Ver todos
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="sgd-table text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Semillero</th>
                            <th class="text-left">Líder</th>
                            <th class="text-left">Integrantes</th>
                            <th class="text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($misSemilleros as $s)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $s->nombre }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $s->leader?->person?->nombre_completo ?? $s->leader?->email ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('dir-sem.semilleros.show', $s) }}" class="text-[#39A900] hover:underline font-medium">{{ $s->members->count() }}</a>
                            </td>
                            <td class="px-5 py-3">
                                @if(($s->estado->value ?? '') === 'activo')
                                <span class="inline-flex items-center gap-1.5 text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                @else
                                <span class="inline-flex items-center gap-1.5 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No hay semilleros a tu cargo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Líderes de Semillero</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Creados por mi</p>
                </div>
                <a href="{{ route('dir-sem.lideres.index') }}" class="sgd-btn-primary px-4 py-2 rounded-xl text-sm font-medium">
                    Ver todos
                </a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($misLideres as $lider)
                @php
                    $semillero = $lider->ledSeedlings->first();
                    $iniciales = $lider->person ? (strtoupper(substr($lider->person->primer_nombre ?? '', 0, 1)) . strtoupper(substr($lider->person->primer_apellido ?? '', 0, 1))) : strtoupper(substr($lider->email ?? 'U', 0, 2));
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-[#39A900]/05 hover:border-[#39A900]/20 transition-all duration-200">
                    <div class="w-10 h-10 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-[#39A900]">{{ $iniciales ?: 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800">{{ $lider->person?->nombre_completo ?? $lider->email ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $semillero?->nombre ?? '—' }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-green-600 text-xs"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">No hay líderes asignados.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Columna derecha: Acciones Rápidas --}}
    <div class="space-y-6">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-base font-semibold text-slate-900">Acciones Rápidas</h2>
            </div>
            <div class="p-4 space-y-2">
                <a href="{{ route('dir-sem.semilleros.create') }}" class="sgd-btn-secondary block w-full text-left px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">Nuevo Semillero</a>
                <a href="{{ route('dir-sem.lideres.create') }}" class="sgd-btn-secondary block w-full text-left px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">Asignar Líder</a>
                <a href="{{ route('dir-sem.documentos.index') }}" class="sgd-btn-secondary block w-full text-left px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">Documentos</a>
                <a href="{{ route('dir-sem.reportes.index') }}" class="sgd-btn-secondary block w-full text-left px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">Reportes</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const ctxResumen = document.getElementById('chart-dir-sem-resumen');
        const ctxEstado = document.getElementById('chart-dir-sem-estado');
        if (!ctxResumen || !ctxEstado || typeof Chart === 'undefined') return;

        const labels = @json($chartResumenLabels ?? []);
        const series = @json($chartResumenSeries ?? []);
        const estadoLabels = @json($chartEstadoSemillerosLabels ?? []);
        const estadoSeries = @json($chartEstadoSemillerosSeries ?? []);

        const gradient = ctxResumen.getContext('2d').createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(57, 169, 0, 0.9)');
        gradient.addColorStop(1, 'rgba(57, 169, 0, 0.35)');

        new Chart(ctxResumen, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad',
                    data: series,
                    borderRadius: 10,
                    borderSkipped: false,
                    backgroundColor: gradient,
                    hoverBackgroundColor: '#2d8500',
                    maxBarThickness: 42,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        callbacks: {
                            label: (ctx) => 'Total: ' + ctx.parsed.y
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', maxRotation: 0, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148,163,184,0.22)' },
                        ticks: { color: '#64748b', precision: 0, stepSize: 1 }
                    }
                }
            }
        });

        new Chart(ctxEstado, {
            type: 'doughnut',
            data: {
                labels: estadoLabels,
                datasets: [{
                    data: estadoSeries,
                    backgroundColor: ['#39A900', '#cbd5e1'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 4,
                    hoverOffset: 8,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, color: '#475569', font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                    }
                }
            }
        });
    })();
</script>
@endpush
