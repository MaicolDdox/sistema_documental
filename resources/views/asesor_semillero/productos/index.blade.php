<x-app-layout>
<x-slot name="header">Productos del Semillero</x-slot>

{{-- Acciones de página y Filtros --}}
<div x-data="{ openExport: false }" class="flex flex-wrap items-center gap-3 mb-6">
    {{-- Filtro por proyecto --}}
    <form method="GET" class="flex gap-2">
        <select name="proyecto" onchange="this.form.submit()" class="border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            <option value="">Todos los proyectos</option>
            @foreach($proyectos as $proy)
                <option value="{{ $proy->id }}" {{ request('proyecto') == $proy->id ? 'selected' : '' }}>{{ Str::limit($proy->nombre, 50) }}</option>
            @endforeach
        </select>
    </form>

    {{-- Botones de acción --}}
    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
        <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Descargar Reportes
        </button>

        {{-- Modal de Exportación --}}
        <div x-show="openExport" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
            <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Descargar Reporte de Productos</h3>
                    <button type="button" @click="openExport = false" class="text-slate-400 hover:text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="GET" action="{{ route('asesor.exportar.productos') }}">
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

        @can('productos.registrar')
            <a href="{{ route('asesor.productos.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
               style="background:#39A900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Registrar Producto
            </a>
        @endcan
    </div>
</div>

@if($productos->isEmpty())
<div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
    <p class="text-slate-500 text-sm">No hay productos registrados.</p>
    @can('productos.registrar')
        <a href="{{ route('asesor.productos.create') }}" class="mt-3 inline-block text-sm font-medium" style="color:#39A900">Registrar el primer producto →</a>
    @endcan
</div>
@else
<div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre del producto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Semillero</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyecto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Autores</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Archivo / Enlace</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $product)
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                {{-- Nombre --}}
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 max-w-[180px] truncate" title="{{ $product->nombre }}">{{ $product->nombre }}</p>
                </td>
                {{-- Semillero --}}
                @php
                    $sem = \Illuminate\Support\Facades\DB::table('project_seedlings')
                        ->join('seedlings','seedlings.id','=','project_seedlings.seedling_id')
                        ->where('project_seedlings.project_id', $product->project_id)
                        ->value('seedlings.nombre');
                @endphp
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-[#39A900] text-xs font-medium">
                        {{ $sem ?? '—' }}
                    </span>
                </td>
                {{-- Proyecto --}}
                <td class="px-4 py-3 text-xs text-slate-600 max-w-[150px]">
                    <span title="{{ $product->project?->nombre }}">{{ Str::limit($product->project?->nombre, 35) ?? '—' }}</span>
                </td>
                {{-- Autores --}}
                <td class="px-4 py-3">
                    @if($product->productAuthors->isNotEmpty())
                    <div class="flex items-center -space-x-1.5">
                        @foreach($product->productAuthors->take(4) as $pa)
                        @php
                            $nombre = trim(($pa->projectAuthor?->user?->person?->primer_nombre ?? '') . ' ' . ($pa->projectAuthor?->user?->person?->primer_apellido ?? ''));
                        @endphp
                        <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background:#0a1628"
                             title="{{ $nombre ?: 'Autor' }}">
                            {{ strtoupper(substr($nombre ?: 'A', 0, 1)) }}
                        </div>
                        @endforeach
                        @if($product->productAuthors->count() > 4)
                        <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center bg-slate-200 text-slate-600 text-xs font-bold">
                            +{{ $product->productAuthors->count() - 4 }}
                        </div>
                        @endif
                    </div>
                    @else
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                {{-- Archivo / URL --}}
                <td class="px-4 py-3 text-xs">
                    <div class="flex flex-col gap-1">
                        @if($product->archivo)
                            <a href="{{ asset('storage/' . $product->archivo) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-blue-600 hover:underline whitespace-nowrap">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                Archivo
                            </a>
                        @endif
                        @if($product->url_repositorio)
                            <a href="{{ $product->url_repositorio }}" target="_blank"
                               class="inline-flex items-center gap-1 text-purple-600 hover:underline whitespace-nowrap">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                Enlace
                            </a>
                        @endif
                        @if(!$product->archivo && !$product->url_repositorio)
                            <span class="text-slate-400">—</span>
                        @endif
                    </div>
                </td>
                {{-- Estado revisión --}}
                <td class="px-4 py-3">
                    @php
                        $er = $product->estado_revision ?? 'pendiente';
                        $badge = match($er) {
                            'aprobado'  => 'bg-green-100 text-green-700',
                            'rechazado' => 'bg-red-100 text-red-700',
                            default     => 'bg-amber-100 text-amber-700',
                        };
                        $dot = match($er) {
                            'aprobado'  => 'bg-green-500',
                            'rechazado' => 'bg-red-500',
                            default     => 'bg-amber-500',
                        };
                        $label = match($er) {
                            'aprobado'  => 'Aprobado',
                            'rechazado' => 'Rechazado',
                            default     => 'Pendiente',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                        <div class="w-1.5 h-1.5 rounded-full {{ $dot }}"></div>
                        {{ $label }}
                    </span>
                </td>
                {{-- Acciones --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                        @can('productos.ver_detalle')
                        <a href="{{ route('asesor.productos.show', $product->id) }}"
                           class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Ver</a>
                        @endcan
                        @can('productos.editar')
                        <a href="{{ route('asesor.productos.edit', $product->id) }}"
                           class="text-xs px-2.5 py-1.5 rounded-lg text-white transition-all hover:opacity-90" style="background:#39A900">Editar</a>
                        @endcan
                    </div>
                </td>
            </tr>

            @endforeach
        </tbody>
    </table>
</div>
@if($productos->hasPages())
<div class="mt-4">{{ $productos->links() }}</div>
@endif
@endif
</x-app-layout>
