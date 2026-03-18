<x-app-layout>
<x-slot name="header">Aprendices del Semillero</x-slot>

{{-- Acciones de página y Buscador --}}
<div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
    <form method="GET" class="flex gap-3 w-full xl:max-w-md">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o documento..."
                   class="w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Filtrar</button>
        @if(request('buscar'))
            <a href="{{ route('asesor.aprendices.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all flex-shrink-0">Limpiar</a>
        @endif
    </form>

    <div x-data="{ openExport: false }" class="flex flex-wrap items-center gap-2">
        <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Descargar Reportes
        </button>

        {{-- Modal de Exportación --}}
        <div x-show="openExport" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
            <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Descargar Reporte de Aprendices</h3>
                    <button type="button" @click="openExport = false" class="text-slate-400 hover:text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="GET" action="{{ route('asesor.exportar.aprendices') }}">
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rango de tiempo (Opcional)</label>
                        <select name="rango" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 outline-none pr-8">
                            <option value="">Todo el histórico</option>
                            <option value="hoy">El día de hoy</option>
                            <option value="semanal">Esta semana</option>
                            <option value="mensual">Este mes</option>
                            <option value="anual">Este año</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="openExport = false" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#39A900] hover:bg-[#2b8000] flex items-center gap-1.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#39A900]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Generar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @can('aprendices.registrar')
            <a href="{{ route('asesor.aprendices.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
               style="background:#39A900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Registrar Aprendiz
            </a>
        @endcan
    </div>
</div>

@if(!$semillero)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <p class="text-amber-800 text-sm">⚠️ No tienes un semillero asignado.</p>
    </div>
@elseif($aprendices->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        <p class="text-slate-500 text-sm">No hay aprendices registrados en tu semillero.</p>
        @can('aprendices.registrar')
            <a href="{{ route('asesor.aprendices.create') }}" class="mt-3 inline-block text-sm font-medium" style="color:#39A900">Registrar el primer aprendiz →</a>
        @endcan
    </div>
@else
<div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[900px]">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre completo</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo doc.</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">N° Documento</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Género</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Celular</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Teléfono</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Correo institucional</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">EPS</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cargo / Rol</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo vinculación</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Programa de formación</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aprendices as $ap)
            @php $p = $ap->person; @endphp
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                {{-- Nombre --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0" style="background:#0a1628">
                            {{ strtoupper(substr($p?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($p?->primer_apellido ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-800 whitespace-nowrap">
                                {{ $p?->primer_nombre }} {{ $p?->segundo_nombre }} {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}
                            </p>
                        </div>
                    </div>
                </td>
                {{-- Tipo doc --}}
                <td class="px-4 py-3 text-slate-600 text-xs capitalize whitespace-nowrap">
                    {{ str_replace(['_', 'cedula '], [' ', 'Cédula '], $ap->tipo_documento?->value ?? '—') }}
                </td>
                {{-- N° Documento --}}
                <td class="px-4 py-3 text-slate-700 font-mono text-xs whitespace-nowrap">
                    {{ $ap->numero_documento }}
                </td>
                {{-- Género --}}
                <td class="px-4 py-3 text-slate-600 text-xs capitalize whitespace-nowrap">
                    {{ $p?->genero ?? '—' }}
                </td>
                {{-- Celular --}}
                <td class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                    {{ $p?->celular ?? '—' }}
                </td>
                {{-- Teléfono --}}
                <td class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                    {{ $p?->telefono ?? '—' }}
                </td>
                {{-- Correo institucional --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    <a href="mailto:{{ $p?->email_institucional }}" class="text-blue-600 hover:underline">
                        {{ $p?->email_institucional ?? '—' }}
                    </a>
                </td>
                {{-- EPS --}}
                <td class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">{{ $p?->eps ?? '—' }}</td>
                {{-- Cargo/Rol --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($p?->entityPosition)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700">{{ $p->entityPosition->nombre }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Tipo vinculación --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($p?->linkageType)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">{{ $p->linkageType->nombre }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Programa de formación --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($p?->trainingProgram)
                        <span class="text-slate-700">{{ $p->trainingProgram->nombre }}</span>
                        @if($p->trainingProgram->trainingProgramType)
                            <span class="block text-slate-400">({{ $p->trainingProgram->trainingProgramType->nombre }})</span>
                        @endif
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Acciones --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        @can('aprendices.ver_detalle')
                        <a href="{{ route('asesor.aprendices.show', $ap->id) }}"
                           class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Ver</a>
                        @endcan
                        @can('aprendices.editar')
                        <a href="{{ route('asesor.aprendices.edit', $ap->id) }}"
                           class="text-xs px-2.5 py-1.5 rounded-lg text-white transition-all hover:opacity-90" style="background:#39A900">Editar</a>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Paginación --}}
@if($aprendices->hasPages())
<div class="mt-4">{{ $aprendices->links() }}</div>
@endif
@endif
</x-app-layout>
