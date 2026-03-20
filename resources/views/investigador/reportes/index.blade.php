<x-app-layout>
    <x-slot name="header">Mis Reportes</x-slot>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mis Reportes</h1>
            <p class="text-sm text-slate-500 mt-0.5">Estadísticas de tu actividad en el grupo</p>
        </div>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-2 font-semibold uppercase tracking-wider">Proyectos</p>
            <p class="text-3xl font-bold text-slate-800">{{ $metricas['total_proyectos'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-2 font-semibold uppercase tracking-wider">Total productos</p>
            <p class="text-3xl font-bold text-slate-800">{{ $metricas['total_productos'] }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5 shadow-sm">
            <p class="text-xs text-green-700 mb-2 font-semibold uppercase tracking-wider">Aprobados</p>
            <p class="text-3xl font-bold text-green-700">{{ $metricas['productos_aprobados'] }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-5 shadow-sm">
            <p class="text-xs text-red-600 mb-2 font-semibold uppercase tracking-wider">Rechazados</p>
            <p class="text-3xl font-bold text-red-700">{{ $metricas['productos_rechazados'] }}</p>
        </div>
    </div>

    {{-- Barras de estado --}}
    @if($metricas['total_productos'] > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Mis Productos por Estado</h2>
        </div>
        @php
            $estados = [
                ['label' => 'Aprobados',   'count' => $metricas['productos_aprobados'],  'color' => 'bg-green-500'],
                ['label' => 'Pendientes',  'count' => $metricas['productos_pendientes'], 'color' => 'bg-amber-400'],
                ['label' => 'En revisión', 'count' => $metricas['productos_revision'],   'color' => 'bg-blue-400'],
                ['label' => 'Rechazados',  'count' => $metricas['productos_rechazados'], 'color' => 'bg-red-400'],
            ];
            $total = $metricas['total_productos'] ?: 1;
        @endphp
        <div class="divide-y divide-slate-50">
            @foreach($estados as $e)
            <div class="flex items-center gap-4 px-5 py-3">
                <span class="flex items-center gap-2 w-28 text-sm text-slate-600 shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full {{ $e['color'] }}"></span>{{ $e['label'] }}
                </span>
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="{{ $e['color'] }} h-2 rounded-full" style="width: {{ round(($e['count'] / $total) * 100) }}%"></div>
                </div>
                <span class="text-sm font-bold text-slate-900 w-5 text-right shrink-0">{{ $e['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Por año --}}
    @if($porAnio->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Mis Productos por Año</h2>
        </div>
        @php $max = $porAnio->max('total') ?: 1; @endphp
        <div class="divide-y divide-slate-50">
            @foreach($porAnio as $item)
            <div class="flex items-center gap-4 px-5 py-3">
                <span class="w-14 text-sm font-mono font-medium text-slate-600 shrink-0">{{ $item->anio ?? '—' }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#39A900] h-2 rounded-full" style="width: {{ round(($item->total / $max) * 100) }}%"></div>
                </div>
                <span class="text-sm font-bold text-slate-900 w-5 text-right shrink-0">{{ $item->total }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabla de productos --}}
    @if($productos->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Mis Productos ({{ $productos->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Título</th>
                        <th class="text-left">Proyecto</th>
                        <th class="text-left">Año</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Tipología</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                    @php $estado = $p->estado_revision?->value ?? $p->estado_revision ?? 'pendiente'; @endphp
                    <tr>
                        <td class="font-medium text-slate-800">{{ $p->titulo }}</td>
                        <td class="text-slate-500">{{ $p->product?->project?->nombre ?? '—' }}</td>
                        <td class="text-slate-600">{{ $p->anio_publicacion ?? '—' }}</td>
                        <td>
                            @php
                                $badgeMap = ['aprobado' => 'bg-green-100 text-green-800', 'pendiente' => 'bg-amber-100 text-amber-800', 'en_revision' => 'bg-blue-100 text-blue-800', 'rechazado' => 'bg-red-100 text-red-800'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $badgeMap[$estado] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </span>
                        </td>
                        <td class="text-slate-500">{{ $p->mincienciasTypology?->nombre ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Panel de descargas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm" x-data="{ tipo: 'aprobados', periodo: '' }">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Descargar Reporte
        </h3>

        <div class="flex flex-wrap gap-4 items-end">
            {{-- Tipo --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Productos a incluir</label>
                <div class="flex gap-2">
                    <button type="button" @click="tipo = 'aprobados'"
                            :class="tipo === 'aprobados' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Solo Aprobados</button>
                    <button type="button" @click="tipo = 'todos'"
                            :class="tipo === 'todos' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Todos</button>
                </div>
            </div>

            {{-- Periodo --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Periodo</label>
                <div class="flex gap-2">
                    <button type="button" @click="periodo = ''"
                            :class="periodo === '' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Todos</button>
                    <button type="button" @click="periodo = 'semanal'"
                            :class="periodo === 'semanal' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Semanal</button>
                    <button type="button" @click="periodo = 'mensual'"
                            :class="periodo === 'mensual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Mensual</button>
                    <button type="button" @click="periodo = 'anual'"
                            :class="periodo === 'anual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Anual</button>
                </div>
            </div>

            {{-- Botones de descarga --}}
            <div class="flex gap-2">
                <button type="button"
                        @click="window.location = '{{ route('investigador.reportes.exportar.csv') }}?tipo=' + tipo + '&periodo=' + periodo"
                        class="flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    CSV (Excel)
                </button>
                <button type="button"
                        @click="window.open('{{ route('investigador.reportes.exportar.pdf') }}?tipo=' + tipo + '&periodo=' + periodo, '_blank')"
                        class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Descargar PDF
                </button>
            </div>
        </div>
    </div>

</x-app-layout>
