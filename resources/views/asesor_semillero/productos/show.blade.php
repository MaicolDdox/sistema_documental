<x-app-layout>
<x-slot name="header">Detalle del Producto</x-slot>


{{-- Modal de vista previa --}}
<div x-data="{
    previewOpen: false,
    previewUrl: '',
    previewNombre: '',
    downloadUrl: '',
    isPdf: false,
    openPreview(url, nombre, downloadUrl) {
        this.previewUrl = url;
        this.previewNombre = nombre;
        this.downloadUrl = downloadUrl;
        this.isPdf = nombre.toLowerCase().endsWith('.pdf');
        this.previewOpen = true;
    }
}" x-cloak>

    {{-- Overlay del modal --}}
    <div x-show="previewOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex flex-col"
         @click.self="previewOpen = false"
         @keydown.escape.window="previewOpen = false">

        {{-- Modal panel --}}
        <div class="relative flex flex-col flex-1 mx-4 my-4 bg-white rounded-2xl shadow-2xl overflow-hidden max-w-5xl w-full self-center">

            {{-- Header del modal --}}
            <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-200 flex-shrink-0">
                <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <span class="text-sm font-medium text-slate-800 flex-1 truncate" x-text="previewNombre"></span>
                <a :href="downloadUrl" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#39A900] text-white text-xs font-semibold hover:bg-[#2d8500] transition-all flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Descargar
                </a>
                <button @click="previewOpen = false" class="text-slate-400 hover:text-slate-700 transition-colors ml-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Vista previa --}}
            <div class="flex-1 overflow-hidden relative">
                {{-- PDF: embed directo --}}
                <iframe x-show="isPdf"
                        :src="previewUrl"
                        class="w-full h-full border-0"
                        style="min-height: 70vh">
                </iframe>
                {{-- Otros formatos: Google Docs Viewer --}}
                <iframe x-show="!isPdf"
                        :src="'https://docs.google.com/viewer?url=' + encodeURIComponent(window.location.origin + previewUrl) + '&embedded=true'"
                        class="w-full h-full border-0"
                        style="min-height: 70vh">
                </iframe>
            </div>
        </div>
    </div>

    {{-- ── Contenido de la página ── --}}
    <div class="mb-4">
        <a href="{{ route('asesor.productos.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Volver a productos
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Columna izquierda: info --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Datos principales --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Información del producto</h3>
                    @can('productos.editar')
                    <a href="{{ route('asesor.productos.edit', $product->id) }}"
                       class="text-xs px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition-all" style="background:#39A900">
                        Editar
                    </a>
                    @endcan
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Nombre / Título</p>
                        <p class="text-slate-800 font-medium">{{ $product->nombre }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Semillero</p>
                            <p class="text-sm text-slate-700">{{ $semilleroNombre ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Proyecto</p>
                            <p class="text-sm text-slate-700">{{ $product->project?->nombre ?? '—' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Revisión del Líder</p>
                        @php
                            $er = $product->estado_revision ?? 'pendiente';
                            $badgeCls = match($er) {
                                'aprobado'  => 'bg-green-100 text-green-700',
                                'rechazado' => 'bg-red-100 text-red-700',
                                default     => 'bg-amber-100 text-amber-700',
                            };
                            $dotCls = match($er) {
                                'aprobado'  => 'bg-green-500',
                                'rechazado' => 'bg-red-500',
                                default     => 'bg-amber-500',
                            };
                            $label = match($er) {
                                'aprobado'  => 'Aprobado',
                                'rechazado' => 'Rechazado',
                                default     => 'En revisión (pendiente)',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $badgeCls }}">
                            <div class="w-2 h-2 rounded-full {{ $dotCls }}"></div>
                            {{ $label }}
                        </span>
                        @if($product->observacion_revision)
                        <div class="mt-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-xs text-slate-500 font-semibold mb-0.5">Comentario del líder:</p>
                            <p class="text-sm text-slate-700">{{ $product->observacion_revision }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Autores --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-900">Autores del producto</h3>
                </div>
                <div class="p-5">
                    @if($autores->isEmpty())
                        <p class="text-sm text-slate-400">Sin autores registrados.</p>
                    @else
                    <div class="space-y-3">
                        @foreach($autores as $pa)
                        @php
                            $p = $pa->projectAuthor?->user?->person;
                            $nombre = trim(($p?->primer_nombre ?? '') . ' ' . ($p?->segundo_nombre ?? '') . ' ' . ($p?->primer_apellido ?? '') . ' ' . ($p?->segundo_apellido ?? ''));
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#0a1628">
                                {{ strtoupper(substr($nombre ?: 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $nombre ?: 'Autor desconocido' }}</p>
                                <p class="text-xs text-slate-400">{{ $pa->projectAuthor?->user?->email ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: archivo --}}
        <div class="space-y-5">

            {{-- Archivo --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-900">Archivo del producto</h3>
                </div>
                <div class="p-5 space-y-3">

                    @if($product->archivo)
                    @php
                        $archivoUrl    = asset('storage/' . $product->archivo);
                        $archivoNombre = $product->archivo_nombre ?? basename($product->archivo);
                        $downloadUrl   = route('asesor.productos.download', $product->id);
                        $ext           = strtolower(pathinfo($archivoNombre, PATHINFO_EXTENSION));
                        $iconColor = match($ext) {
                            'pdf'  => 'text-red-500',
                            'docx', 'doc' => 'text-blue-600',
                            'xlsx', 'xls' => 'text-green-600',
                            'pptx', 'ppt' => 'text-orange-500',
                            default       => 'text-slate-500',
                        };
                    @endphp
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-50 flex-shrink-0">
                                <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate" title="{{ $archivoNombre }}">
                                    {{ $archivoNombre }}
                                </p>
                                <p class="text-xs text-slate-400 uppercase">{{ $ext }}</p>
                            </div>
                        </div>
                        {{-- Botones: Vista previa + Descargar --}}
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="openPreview('{{ $archivoUrl }}', '{{ $archivoNombre }}', '{{ $downloadUrl }}')"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Vista previa
                            </button>
                            <a href="{{ $downloadUrl }}"
                               class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90" style="background:#39A900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                                Descargar
                            </a>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-slate-400">Sin archivo físico subido.</p>
                    @endif

                    @if($product->url_repositorio)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <p class="text-xs text-slate-400 mb-2 font-semibold uppercase">Enlace externo</p>
                        <a href="{{ $product->url_repositorio }}" target="_blank"
                           class="inline-flex items-center gap-2 text-sm text-purple-600 hover:underline break-all">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                            </svg>
                            {{ Str::limit($product->url_repositorio, 60) }}
                        </a>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Evidencias rápidas --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Evidencias</h3>
                    @can('evidencias.listar')
                    <a href="{{ route('asesor.evidencias.producto.index', $product->id) }}"
                       class="text-xs text-slate-500 hover:text-slate-700 transition-colors">Ver todas →</a>
                    @endcan
                </div>
                <div class="px-5 py-4">
                    @if($product->productEvidences->isEmpty())
                        <p class="text-sm text-slate-400">Sin evidencias.</p>
                    @else
                        <p class="text-sm text-slate-700 font-medium">{{ $product->productEvidences->count() }} evidencia(s) registradas</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
</x-app-layout>
