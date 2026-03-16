<x-app-layout>
<x-slot name="header">Aprendices del Semillero</x-slot>

{{-- Acciones de página --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <div>
<div x-data="{ openExport: false }" class="flex items-center gap-2">
    <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Descargar Reportes
    </button>

    {{-- Modal de Exportación --}}
    <div x-show="openExport" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Descargar Reporte de Aprendices</h3>
                <button @click="openExport = false" class="text-slate-400 hover:text-red-500">
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
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
           style="background:#39A900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Registrar Aprendiz
        </a>
    @endcan
</div>
    </div>
</div>


{{-- Botón Registrar en la parte superior --}}
@can('aprendices.registrar')
<div class="mb-4 flex justify-end">
    <button type="button"
            @click="openCreate = true"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
            style="background:#39A900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Registrar Aprendiz
    </button>
</div>
@endcan
{{-- Buscador --}}
<form method="GET" class="mb-4 flex gap-3">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o documento..."
               class="w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
    </div>
    <button type="submit" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Filtrar</button>
    @if(request('buscar'))
        <a href="{{ route('asesor.aprendices.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">Limpiar</a>
    @endif
</form>

@if(!$semillero)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <p class="text-amber-800 text-sm">⚠️ No tienes un semillero asignado.</p>
    </div>
@elseif($aprendices->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        <p class="text-slate-500 text-sm">No hay aprendices registrados en tu semillero.</p>
        @can('aprendices.registrar')
            <button type="button"
                    @click="openCreate = true"
                    class="mt-3 inline-block text-sm font-medium"
                    style="color:#39A900">
                Registrar el primer aprendiz →
            </button>
        @endcan
    </div>
@else
<div class="sgd-table-card bg-white overflow-x-auto rounded-xl border border-slate-200/80 shadow-sm">
    <table class="sgd-table aprendices-table min-w-[720px] table-fixed">
        <thead>
            <tr>
                <th class="w-[28%] text-left py-3.5 px-4">Aprendiz</th>
                <th class="w-[18%] text-left py-3.5 px-4">Documento</th>
                <th class="w-[22%] text-left py-3.5 px-4">Correo institucional</th>
                <th class="w-[12%] text-left py-3.5 px-4">Celular</th>
                <th class="w-[20%] text-right py-3.5 px-4 pr-5">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
            @foreach($aprendices as $ap)
            @php $p = $ap->person; @endphp
            <tr class="hover:bg-emerald-50/50 transition-colors">
                {{-- Aprendiz: nombre + programa --}}
                <td class="py-4 px-4 align-middle text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, #0a1628 0%, #1e3a5f 100%);">
                            {{ strtoupper(substr($p?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($p?->primer_apellido ?? 'P', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ $p?->primer_nombre }} {{ $p?->segundo_nombre }} {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $p?->trainingProgram?->nombre ?? 'Sin programa' }}
                            </p>
                        </div>
                    </div>
                </td>
                {{-- Documento: tipo + número --}}
                <td class="py-4 px-4 align-middle text-left">
                    <div class="space-y-0.5">
                        <p class="text-xs text-slate-500 capitalize">
                            {{ str_replace(['_', 'cedula '], [' ', 'Cédula '], $ap->tipo_documento?->value ?? '—') }}
                        </p>
                        <p class="text-sm font-mono font-medium text-slate-700">{{ $ap->numero_documento }}</p>
                    </div>
                </td>
                {{-- Correo --}}
                <td class="py-4 px-4 align-middle text-left">
                    <a href="mailto:{{ $p?->email_institucional }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline truncate block max-w-[200px]" title="{{ $p?->email_institucional ?? '—' }}">
                        {{ $p?->email_institucional ?? '—' }}
                    </a>
                </td>
                {{-- Celular --}}
                <td class="py-4 px-4 align-middle text-left">
                    @if($p?->celular)
                        <a href="tel:{{ $p?->celular }}" class="text-sm text-slate-700 hover:text-emerald-600 font-medium">{{ $p?->celular }}</a>
                    @else
                        <span class="text-slate-400 text-sm">—</span>
                    @endif
                </td>
                {{-- Acciones --}}
                <td class="py-4 px-4 pr-5 align-middle text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button type="button"
                                @click="showDetailId = {{ $ap->id }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-all"
                                title="Ver detalles">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s2.25-6.75 9.75-6.75S21.75 12 21.75 12 19.5 18.75 12 18.75 2.25 12 2.25 12z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
                        <button type="button"
                                @click="editId = {{ $ap->id }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-emerald-200 text-emerald-600 bg-emerald-50/80 hover:bg-emerald-100 transition-all"
                                title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a1.5 1.5 0 012.121 2.121L8.621 15.97a4.5 4.5 0 01-1.591 1.03L5 17.75l.75-2.03a4.5 4.5 0 011.03-1.591l10.082-10.642z" />
                            </svg>
                        </button>
                        <form method="POST" id="delete-form-{{ $ap->id }}" action="{{ route('asesor.aprendices.destroy', $ap->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    @click="confirmDeleteId = {{ $ap->id }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 text-red-500 bg-red-50/50 hover:bg-red-100 transition-all"
                                    title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
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
