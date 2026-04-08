<x-app-layout>
    <x-slot name="header">Reportes del Grupo</x-slot>

    @php
        $grupo    = $actividadGeneral['grupo'] ?? null;
        $sede     = $grupo?->trainingCenter?->nombre ?? '—';
        $badgeMap = ['aprobado' => 'bg-green-100 text-green-800','pendiente' => 'bg-amber-100 text-amber-800','en_revision' => 'bg-blue-100 text-blue-800','rechazado' => 'bg-red-100 text-red-800'];
    @endphp

    {{-- ── CABECERA ─────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reportes del Grupo</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $grupo?->nombre ?? 'Grupo' }} · <span class="text-[#39A900] font-medium">{{ $sede }}</span>
            </p>
        </div>
        <div class="text-xs text-slate-400">Actualizado: {{ now()->translatedFormat('d M Y, H:i') }}</div>
    </div>

    {{-- ── KPI TARJETAS ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @php
        $kpis = [
            ['label'=>'Investigadores', 'value'=>$actividadGeneral['total_investigadores'], 'color'=>'text-slate-900',  'bg'=>'bg-white',        'icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['label'=>'Productos',      'value'=>$actividadGeneral['total_productos'],      'color'=>'text-slate-900',  'bg'=>'bg-white',        'icon'=>'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
            ['label'=>'Aprobados',      'value'=>$actividadGeneral['productos_aprobados'],  'color'=>'text-green-700',  'bg'=>'bg-green-50 border-green-100',   'icon'=>'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['label'=>'Pendientes',     'value'=>$aprobadosVsRechazados['pendiente'],       'color'=>'text-amber-700',  'bg'=>'bg-amber-50 border-amber-100',   'icon'=>'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['label'=>'Proyectos',      'value'=>$actividadGeneral['total_proyectos'],      'color'=>'text-blue-700',   'bg'=>'bg-blue-50 border-blue-100',     'icon'=>'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44z'],
            ['label'=>'Macroproyectos', 'value'=>$actividadGeneral['total_macroproyectos'],'color'=>'text-purple-700', 'bg'=>'bg-purple-50 border-purple-100', 'icon'=>'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="rounded-xl border border-slate-200 p-4 shadow-sm {{ $kpi['bg'] }} flex flex-col gap-1">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</p>
                <svg class="w-4 h-4 {{ $kpi['color'] }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/></svg>
            </div>
            <p class="text-3xl font-bold {{ $kpi['color'] }}">{{ $kpi['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── TABS ─────────────────────────────────────────────────────────── --}}
    <div x-data="initReportesData()" class="space-y-6">

        <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit flex-wrap">
            @foreach([['productos','Productos'],['proyectos','Proyectos'],['macros','Macroproyectos'],['investigadores','Investigadores']] as [$key,$label])
            <button type="button" @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-white shadow text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ══ TAB: PRODUCTOS ════════════════════════════════════════════ --}}
        <div x-show="tab === 'productos'" x-cloak>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('director.reportes.index') }}"
                  class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                    <select name="estado_revision" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="">Todos</option>
                        <option value="pendiente"   {{ request('estado_revision') === 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_revision" {{ request('estado_revision') === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                        <option value="aprobado"    {{ request('estado_revision') === 'aprobado'    ? 'selected' : '' }}>Aprobado</option>
                        <option value="rechazado"   {{ request('estado_revision') === 'rechazado'   ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Investigador</label>
                    <select name="investigador_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 min-w-[180px]">
                        <option value="">Todos</option>
                        @foreach($miembros as $m)
                        <option value="{{ $m['id'] }}" {{ request('investigador_id') == $m['id'] ? 'selected' : '' }}>{{ $m['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Año</label>
                    <div class="flex items-center gap-2 flex-wrap">
                        <input type="number" name="anio" id="anio-inp" value="{{ request('anio') }}" min="2000" max="{{ date('Y') + 5 }}" placeholder="{{ date('Y') }}"
                               class="w-24 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <button type="button" onclick="document.getElementById('anio-inp').value='{{ $y }}'"
                                class="px-2 py-1.5 text-xs rounded-lg border {{ request('anio') == $y ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]' }} transition-all">{{ $y }}</button>
                        @endfor
                    </div>
                </div>
                <button type="submit" class="sgd-btn-primary px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
                @if(request()->hasAny(['estado_revision','anio','investigador_id']))
                    <a href="{{ route('director.reportes.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Limpiar</a>
                @endif
            </form>

            {{-- Distribución de estados --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Distribución por Estado</h3>
                    <canvas id="chartEstados" height="220"></canvas>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Producción por Año</h3>
                    <canvas id="chartAnios" height="220"></canvas>
                </div>
            </div>

            {{-- Tabla de productos --}}
            @if($productos->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Productos ({{ $productos->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Investigador</th>
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
                                <td class="text-slate-600 text-xs">{{ $p->author?->person?->nombre_completo ?? $p->author?->email ?? '—' }}</td>
                                <td class="font-medium text-slate-800">{{ $p->titulo }}</td>
                                <td class="text-slate-500 text-xs">{{ $p->product?->project?->nombre ?? '—' }}</td>
                                <td class="text-slate-600">{{ $p->anio_publicacion ?? '—' }}</td>
                                <td>
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $badgeMap[$estado] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </span>
                                </td>
                                <td class="text-slate-500 text-xs">{{ $p->mincienciasTypology?->nombre ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-slate-200 py-10 text-center text-slate-400 text-sm mb-5">
                No hay productos con los filtros aplicados.
            </div>
            @endif

            {{-- Por investigador --}}
            @if(count($porInvestigador))
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Producción por Investigador</h2>
                </div>
                @php $maxInv = collect($porInvestigador)->max('total') ?: 1; @endphp
                <div class="divide-y divide-slate-50">
                    @foreach($porInvestigador as $item)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="w-8 h-8 rounded-full bg-[#39A900]/15 flex items-center justify-center shrink-0 text-xs font-bold text-[#39A900]">
                            {{ strtoupper(substr($item['investigador'] ?? 'U', 0, 1)) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $item['investigador'] }}</p>
                            <div class="mt-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#39A900] h-1.5 rounded-full" style="width: {{ round(($item['total'] / $maxInv) * 100) }}%"></div>
                            </div>
                        </div>
                        <span class="text-base font-bold text-slate-900 shrink-0">{{ $item['total'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ══ TAB: PROYECTOS ════════════════════════════════════════════ --}}
        <div x-show="tab === 'proyectos'" x-cloak>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Proyectos</p>
                    <p class="text-3xl font-bold text-blue-700">{{ $proyectos['total'] }}</p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-green-600 mb-1">Activos</p>
                    <p class="text-3xl font-bold text-green-700">{{ $proyectos['activos'] }}</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Inactivos</p>
                    <p class="text-3xl font-bold text-slate-600">{{ $proyectos['inactivos'] }}</p>
                </div>
            </div>

            @if($proyectos['lista']->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Lista de Proyectos del Grupo</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Nombre</th>
                                <th class="text-left">Línea de investigación</th>
                                <th class="text-left">Macroproyecto</th>
                                <th class="text-left">Creador</th>
                                <th class="text-left">Inicio</th>
                                <th class="text-left">Fin</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proyectos['lista'] as $proy)
                            @php $estProy = $proy->estado?->value ?? 'inactivo'; @endphp
                            <tr>
                                <td class="font-medium text-slate-800">{{ $proy->nombre }}</td>
                                <td class="text-slate-500 text-xs">{{ $proy->researchLine?->nombre ?? '—' }}</td>
                                <td class="text-slate-500 text-xs">{{ $proy->macroProject?->nombre ?? '—' }}</td>
                                <td class="text-slate-600 text-xs">{{ $proy->projectCreator?->person?->nombre_completo ?? $proy->projectCreator?->email ?? '—' }}</td>
                                <td class="text-slate-600 text-xs">{{ $proy->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-slate-600 text-xs">{{ $proy->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $estProy === 'activo' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($estProy) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-slate-200 py-14 text-center text-slate-400 text-sm">
                No hay proyectos vinculados a este grupo aún.
            </div>
            @endif
        </div>

        {{-- ══ TAB: MACROPROYECTOS ═══════════════════════════════════════ --}}
        <div x-show="tab === 'macros'" x-cloak>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-1">Total Macroproyectos</p>
                    <p class="text-3xl font-bold text-purple-700">{{ $macroproyectos['total'] }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3"/></svg>
                    </div>
                    <p class="text-sm text-slate-600">Macroproyectos a los que pertenecen proyectos de este grupo.</p>
                </div>
            </div>

            @if($macroproyectos['lista']->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Macroproyectos del Grupo</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Nombre</th>
                                <th class="text-left">Proyectos vinculados</th>
                                <th class="text-left">Código</th>
                                <th class="text-left">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($macroproyectos['lista'] as $macro)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $macro->nombre }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        {{ $macro->projects_count ?? '—' }} proyectos
                                    </span>
                                </td>
                                <td class="text-slate-500 text-xs font-mono">{{ $macro->codigo ?? '—' }}</td>
                                <td class="text-slate-500 text-xs max-w-xs truncate">{{ $macro->descripcion ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-slate-200 py-14 text-center text-slate-400 text-sm">
                No hay macroproyectos vinculados a este grupo aún.
            </div>
            @endif
        </div>

        {{-- ══ TAB: INVESTIGADORES ═══════════════════════════════════════ --}}
        <div x-show="tab === 'investigadores'" x-cloak>
            @if($investigadoresDetalle->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Detalle de Investigadores ({{ $investigadoresDetalle->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Investigador</th>
                                <th class="text-left">Correo</th>
                                <th class="text-left">Rol</th>
                                <th class="text-center">Total Prod.</th>
                                <th class="text-center">Aprobados</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center">Rechazados</th>
                                <th class="text-left">Estado</th>
                                <th class="text-left">CvLAC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($investigadoresDetalle as $inv)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                                            <span class="text-[10px] font-bold text-[#39A900]">{{ strtoupper(substr($inv['nombre'] ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <span class="font-medium text-slate-800">{{ $inv['nombre'] }}</span>
                                    </div>
                                </td>
                                <td class="text-slate-500 text-xs">{{ $inv['email'] }}</td>
                                <td class="text-xs">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-medium">{{ ucfirst(str_replace('_', ' ', $inv['rol'])) }}</span>
                                </td>
                                <td class="text-center font-bold text-slate-900">{{ $inv['total'] }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $inv['aprobados'] > 0 ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-500' }}">{{ $inv['aprobados'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $inv['pendientes'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' }}">{{ $inv['pendientes'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $inv['rechazados'] > 0 ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-500' }}">{{ $inv['rechazados'] }}</span>
                                </td>
                                <td>
                                    @if($inv['estado'] === 'activo')
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Activo</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inv['cvlac'])
                                        <a href="{{ $inv['cvlac'] }}" target="_blank" class="text-[#39A900] hover:underline text-xs font-medium">Ver perfil →</a>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-slate-200 py-14 text-center text-slate-400 text-sm">
                No hay investigadores en este grupo.
            </div>
            @endif
        </div>
    {{-- /tabs eliminado temporalmente para expandir contenedor --}}
    {{-- ── PANEL DE DESCARGAS ─────────────────────────────────────────── --}}
    <div class="mt-6 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Exportar Reporte
        </h3>

        <div class="flex flex-wrap gap-4 items-end">
            <div x-show="tab === 'productos'">
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Agrupación de Productos</label>
                <div class="flex gap-2 flex-wrap">
                    @foreach([['general','General'],['por_investigador','Por Investigador'],['por_anio','Por Año']] as [$key,$lbl])
                    <button type="button" @click="tipo = '{{ $key }}'"
                            :class="tipo === '{{ $key }}' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>

            <div x-show="tab !== 'productos'" x-cloak>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Tipo de reporte</label>
                <div class="px-3 py-1.5 rounded-lg text-xs font-semibold border bg-slate-50 text-slate-700 border-slate-200">
                    <span x-show="tab === 'proyectos'">Exportar Proyectos</span>
                    <span x-show="tab === 'macros'">Exportar Macroproyectos</span>
                    <span x-show="tab === 'investigadores'">Exportar Investigadores Detalle</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Período</label>
                <div class="flex gap-2 flex-wrap mb-1.5">
                    @foreach([['','Todos'],['semanal','Semanal'],['mensual','Mensual'],['anual','Anual']] as [$key,$lbl])
                    <button type="button" @click="setPeriodo('{{ $key }}')"
                            :class="['{{ $key }}', 'personalizado'].includes(periodo) && '{{ $key }}' !== '' && periodo !== '' || periodo === '{{ $key }}' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">{{ $lbl }}</button>
                    @endforeach
                </div>

                <div x-show="periodo !== ''" x-cloak class="flex items-center gap-2 mt-2 bg-slate-50 p-2 rounded-lg border border-slate-200 w-fit">
                    <div class="flex items-center gap-2">
                        <label class="text-[10px] font-semibold text-slate-500 uppercase flex-shrink-0">Desde</label>
                        <input type="date" x-model="fecha_desde" @input="periodo = 'personalizado'" class="text-xs border-slate-300 rounded focus:ring-[#39A900] focus:border-[#39A900] py-1 px-2 h-7" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-[10px] font-semibold text-slate-500 uppercase flex-shrink-0">Hasta</label>
                        <input type="date" x-model="fecha_hasta" @input="periodo = 'personalizado'" class="text-xs border-slate-300 rounded focus:ring-[#39A900] focus:border-[#39A900] py-1 px-2 h-7" :min="fecha_desde" max="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <div class="flex gap-2 relative z-10">
                <button type="button"
                        @click="window.location.href = exportUrl('{{ route('director.reportes.exportar.csv') }}')"
                        class="flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow h-10 mt-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    CSV (Excel)
                </button>
                <button type="button"
                        @click="window.open(exportUrl('{{ route('director.reportes.exportar.pdf') }}'), '_blank')"
                        class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow h-10 mt-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    PDF
                </button>
            </div>
        </div>
    </div>
    
    </div>{{-- /initReportesData() wrapper --}}

    <script>
        function initReportesData() {
            return {
                tab: 'productos',
                tipo: '{{ request('tipo', 'general') }}',
                periodo: '{{ request('periodo', '') }}',
                fecha_desde: '{{ request('desde', '') }}',
                fecha_hasta: '{{ request('hasta', '') }}',
                
                setPeriodo(p) {
                    this.periodo = p;
                    if (!p) {
                        this.fecha_desde = '';
                        this.fecha_hasta = '';
                        return;
                    }
                    
                    let hoy = new Date();
                    let desde = new Date();
                    
                    if (p === 'semanal') {
                        let dia = hoy.getDay() || 7; 
                        if (dia !== 1) desde.setDate(hoy.getDate() - dia + 1);
                    } else if (p === 'mensual') {
                        desde.setDate(1);
                    } else if (p === 'anual') {
                        desde.setMonth(0, 1);
                    }
                    
                    /* Prevenir error de zona horaria restando un dia extra */
                    let localDesde = new Date(desde.getTime() - (desde.getTimezoneOffset() * 60000));
                    let localHoy = new Date(hoy.getTime() - (hoy.getTimezoneOffset() * 60000));
                    
                    this.fecha_desde = localDesde.toISOString().split('T')[0];
                    this.fecha_hasta = localHoy.toISOString().split('T')[0];
                },
                
                exportUrl(base) {
                    let exportTipo = this.tab === 'proyectos' ? 'por_proyecto' : (this.tab === 'macros' ? 'por_macroproyecto' : (this.tab === 'investigadores' ? 'investigadores_detalle' : this.tipo));
                    let url = base + '?tipo=' + exportTipo;
                    
                    if (this.periodo) {
                        url += '&periodo=' + this.periodo;
                    }
                    if (this.periodo === 'personalizado') {
                        url += '&desde=' + this.fecha_desde + '&hasta=' + this.fecha_hasta;
                    }
                    
                    let estado = '{{ request('estado_revision', '') }}';
                    if (estado) url += '&estado_revision=' + estado;
                    
                    let anio = '{{ request('anio', '') }}';
                    if (anio) url += '&anio=' + anio;
                    
                    let inv_id = '{{ request('investigador_id', '') }}';
                    if (inv_id) url += '&investigador_id=' + inv_id;
                    
                    url += '&_t=' + Date.now();
                    return url;
                }
            };
        }
    </script>

    {{-- ── CHART.JS ───────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const verde  = '#39A900';
        const amber  = '#f59e0b';
        const rojo   = '#ef4444';
        const azul   = '#3b82f6';
        const slate  = '#94a3b8';

        // Donut — estados
        const ctxE = document.getElementById('chartEstados');
        if (ctxE) {
            new Chart(ctxE, {
                type: 'doughnut',
                data: {
                    labels: ['Aprobados', 'Pendientes', 'En revisión', 'Rechazados'],
                    datasets: [{
                        data: [
                            {{ $aprobadosVsRechazados['aprobado'] }},
                            {{ $aprobadosVsRechazados['pendiente'] }},
                            {{ $aprobadosVsRechazados['en_revision'] }},
                            {{ $aprobadosVsRechazados['rechazado'] }},
                        ],
                        backgroundColor: [verde, amber, azul, rojo],
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    cutout: '65%',
                    plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } },
                    responsive: true, maintainAspectRatio: true,
                }
            });
        }

        // Barras — producción por año
        const ctxA = document.getElementById('chartAnios');
        if (ctxA) {
            const labels = {!! $porAnio->pluck('anio_publicacion')->map(fn($a) => $a ?? 'Sin año')->toJson() !!};
            const data   = {!! $porAnio->pluck('total')->toJson() !!};
            new Chart(ctxA, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Productos', data, backgroundColor: verde + 'cc', borderColor: verde, borderWidth: 1.5, borderRadius: 6 }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } }, x: { ticks: { font: { size: 11 } } } },
                    responsive: true, maintainAspectRatio: true,
                }
            });
        }
    })();
    </script>

</x-app-layout>
