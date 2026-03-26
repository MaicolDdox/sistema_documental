<x-app-layout>
<x-slot name="header">Productos del Semillero</x-slot>

<div x-data="{
        openExport: false,
        openModal: false,
        modalUrl: null,
        modalTitle: '',
        openInModal(url, title) { this.modalUrl = url; this.modalTitle = title || ''; this.openModal = true; },
        closeModal() { this.openModal = false; this.modalUrl = null; this.modalTitle = ''; },
        openDelete: false,
        deleteUrl: null,
        deleteTitle: '',
        askDelete(url, title) { this.deleteUrl = url; this.deleteTitle = title || ''; this.openDelete = true; },
        closeDelete() { this.openDelete = false; this.deleteUrl = null; this.deleteTitle = ''; },
        init() { window.prodUI = this; }
    }"
    @keydown.escape.window="if (openModal) closeModal(); if (openDelete) closeDelete();"
>

{{-- Acciones de página y Filtros --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
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

{{-- Modal Ver / Editar (iframe) --}}
<template x-teleport="body">
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-[120] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[90vh] overflow-hidden"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="modalTitle || 'Detalle'"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="modalUrl" target="_blank"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition"
                       title="Abrir en pestaña">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                    <button type="button" @click="closeModal()"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition"
                            title="Cerrar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-white">
                <iframe :src="modalUrl"
                        class="w-full h-[78vh] bg-white"
                        style="border:0"
                        title="Detalle"></iframe>
            </div>
        </div>
    </div>
</template>

{{-- Modal Confirmar eliminación --}}
<template x-teleport="body">
    <div x-show="openDelete" x-cloak
         class="fixed inset-0 z-[130] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" @click="closeDelete()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden"
             @click.stop>
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12M9 7.5V6a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 6v1.5m-7.5 0l.7 13.3A2.25 2.25 0 0010.44 23h3.12a2.25 2.25 0 002.24-2.2L16.5 7.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-900">Eliminar producto</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Esta acción no se puede deshacer.</p>
                    </div>
                    <button type="button" @click="closeDelete()"
                            class="ml-auto inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition"
                            title="Cerrar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-slate-700">
                    ¿Seguro que deseas eliminar
                    <span class="font-semibold text-slate-900" x-text="deleteTitle || 'este producto'"></span>?
                </p>
                <p class="text-xs text-slate-500 mt-2">
                    Se eliminarán también los autores asociados, evidencias y archivos adjuntos.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-white flex items-center justify-end gap-2">
                <button type="button" @click="closeDelete()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <form :action="deleteUrl" method="POST" @submit="closeDelete()">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

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
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado revisión</th>
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
                    @php
                        $autoresNombres = $product->productAuthors
                            ->map(function ($pa) {
                                $u = $pa->projectAuthor?->user;
                                $p = $u?->person;
                                $nombre = trim(($p?->primer_nombre ?? '') . ' ' . ($p?->primer_apellido ?? ''));
                                return $nombre ?: ($u?->email ?? 'Autor');
                            })
                            ->filter()
                            ->values();
                        $autoresPreview = $autoresNombres->take(3);
                        $autoresLabel = $autoresNombres->join(', ');
                    @endphp
                    <div class="flex items-center gap-2">
                        <div class="flex items-center -space-x-1.5" title="{{ $autoresLabel }}">
                            @foreach($autoresPreview as $nombre)
                            <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:#0a1628"
                                 title="{{ $nombre }}">
                                {{ strtoupper(substr($nombre ?: 'A', 0, 1)) }}
                            </div>
                            @endforeach
                            @if($autoresNombres->count() > 3)
                            <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center bg-slate-200 text-slate-600 text-xs font-bold"
                                 title="{{ $autoresLabel }}">
                                +{{ $autoresNombres->count() - 3 }}
                            </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-700 truncate max-w-[180px]" title="{{ $autoresLabel }}">
                                {{ $autoresLabel ?: 'Autor' }}
                            </p>
                        </div>
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
                {{-- Estado --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($product->estado?->value === 'activo')
                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Activo</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500">Inactivo</span>
                    @endif
                </td>
                {{-- Acciones --}}
                <td class="px-4 py-3 text-center">
                    <button
                        onclick="toggleProdMenu(event, this)"
                        data-nombre="{{ $product->nombre }}"
                        data-ver="{{ route('asesor.productos.show', $product->id) }}"
                        data-editar="{{ route('asesor.productos.edit', $product->id) }}"
                        data-deactivate="{{ route('asesor.productos.deactivate', $product->id) }}"
                        data-destroy="{{ route('asesor.productos.destroy', $product->id) }}"
                        data-estado="{{ $product->estado?->value }}"
                        data-can-ver="{{ auth()->user()->can('productos.ver_detalle') ? '1' : '0' }}"
                        data-can-editar="{{ auth()->user()->can('productos.editar') ? '1' : '0' }}"
                        class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 transition-all"
                        title="Acciones">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                        </svg>
                    </button>
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
</div>
</x-app-layout>

{{-- Menú global de acciones (position:fixed, fuera del overflow de la tabla) --}}
<div id="prod-actions-menu"
     style="display:none; position:fixed; z-index:9999; min-width:176px;"
     class="bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden py-1">

    <a id="pam-ver" href="#"
       class="flex items-center gap-2 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Ver detalle
    </a>
    <a id="pam-editar" href="#"
       class="flex items-center gap-2 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
        Editar
    </a>
    <div id="pam-sep" class="border-t border-slate-100 mx-3 my-1"></div>
    <button id="pam-deactivate" type="button" onclick="submitProdDeactivate()"
            class="flex items-center gap-2 w-full px-3 py-2.5 text-sm transition-colors text-amber-600 hover:bg-amber-50">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        <span id="pam-deactivate-label">Desactivar</span>
    </button>
    <button id="pam-destroy" type="button" onclick="submitProdDestroy()"
            class="flex items-center gap-2 w-full px-3 py-2.5 text-sm transition-colors text-red-600 hover:bg-red-50">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
        Eliminar
    </button>
</div>

<form id="prod-action-form" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="_method" id="prod-action-method" value="">
</form>

<script>
let _prodPamUrl = '';
let _prodPamEstado = '';
let _prodPamMethod = '';

function toggleProdMenu(e, btn) {
    e.stopPropagation();
    const menu = document.getElementById('prod-actions-menu');

    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }

    const ver         = btn.dataset.ver;
    const editar      = btn.dataset.editar;
    const canVer      = btn.dataset.canVer === '1';
    const canEdit     = btn.dataset.canEditar === '1';
    const nombre      = btn.dataset.nombre;
    
    _prodPamEstado = btn.dataset.estado;

    const pamVer = document.getElementById('pam-ver');
    pamVer.onclick = (ev) => { ev.preventDefault(); window.prodUI.openInModal(ver, 'Ver producto: ' + nombre); menu.style.display = 'none'; };
    pamVer.style.display = canVer ? 'flex' : 'none';

    const pamEdit = document.getElementById('pam-editar');
    pamEdit.onclick = (ev) => { ev.preventDefault(); window.prodUI.openInModal(editar, 'Editar producto: ' + nombre); menu.style.display = 'none'; };
    pamEdit.style.display = canEdit ? 'flex' : 'none';

    const pamDea  = document.getElementById('pam-deactivate');
    const pamDest = document.getElementById('pam-destroy');
    const pamSep  = document.getElementById('pam-sep');
    const pamLabel = document.getElementById('pam-deactivate-label');
    
    if (canEdit) {
        pamLabel.textContent = _prodPamEstado === 'activo' ? 'Desactivar' : 'Activar';
        pamDea.className = 'flex items-center gap-2 w-full px-3 py-2.5 text-sm transition-colors ' +
            (_prodPamEstado === 'activo' ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50');
        pamDea.style.display = 'flex';
        pamDest.style.display = 'flex';
        pamSep.style.display = 'block';
        
        // Asignamos las URLs a los botones como data atributes para poder extraerlas on click
        pamDea.dataset.actionUrl = btn.dataset.deactivate;
        pamDest.onclick = (ev) => { ev.preventDefault(); window.prodUI.askDelete(btn.dataset.destroy, nombre); menu.style.display = 'none'; };
    } else {
        pamDea.style.display = 'none';
        pamDest.style.display = 'none';
        pamSep.style.display = 'none';
    }

    const r = btn.getBoundingClientRect();
    menu.style.top  = (r.bottom + 4) + 'px';
    menu.style.left = (r.right - 176) + 'px';
    menu.style.display = 'block';
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('prod-actions-menu');
    // Si el clic no fue dentro del menú, lo cerramos
    if (menu && menu.style.display === 'block' && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});

function submitProdDeactivate() {
    const btn = document.getElementById('pam-deactivate');
    if(confirm('¿Confirmas cambiar el estado de este producto?')) {
        const form = document.getElementById('prod-action-form');
        form.action = btn.dataset.actionUrl;
        document.getElementById('prod-action-method').value = 'PATCH';
        form.submit();
    }
}

</script>
