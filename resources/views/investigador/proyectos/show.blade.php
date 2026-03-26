<x-app-layout>
    <x-slot name="header">Detalle del Proyecto</x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Encabezado del proyecto --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $proyecto->nombre }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $proyecto->descripccion ?? 'Sin descripción.' }}</p>
                    @if($proyecto->vinculacion_macro_proyecto)
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#f0fdf4] text-[#39A900]">
                            Vinculado a macro-proyecto
                        </span>
                    @endif
                    @if($proyecto->fecha_fin && $proyecto->fecha_fin < now())
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 ml-1">
                            Proyecto Finalizado
                        </span>
                    @else
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 ml-1">
                            En ejecución
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if(!($proyecto->fecha_fin && $proyecto->fecha_fin <= now()))
                    {{-- Botón Finalizar proyecto --}}
                    <form method="POST" action="{{ route('investigador.proyectos.finalizar', $proyecto) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                onclick="return confirm('¿Estás seguro de finalizar este proyecto? Esta acción lo marcará como terminado y permitirá registrar un producto final.')"
                                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-lg text-sm transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            Finalizar proyecto
                        </button>
                    </form>
                    @else
                    {{-- Si ya está finalizado, acceso rápido al módulo de productos --}}
                    <a href="{{ route('investigador.productos.index') }}"
                       class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-3 rounded-lg text-sm transition-all">
                        Ver mis productos
                    </a>
                    @endif
                    <a href="{{ route('investigador.proyectos.edit', $proyecto) }}"
                       class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-sm transition-all">
                        Editar
                    </a>
                </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-5 border-t border-slate-100">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Línea de investigación</p>
                    <p class="text-sm text-slate-700 mt-0.5 font-medium">{{ $proyecto->researchLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Fecha inicio</p>
                    <p class="text-sm text-slate-700 mt-0.5 font-medium">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Fecha fin</p>
                    <p class="text-sm text-slate-700 mt-0.5 font-medium">{{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Autores</p>
                    <p class="text-sm text-slate-700 mt-0.5 font-medium">{{ $proyecto->projectAuthors->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Productos del proyecto --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Productos registrados</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $proyecto->products->count() }} producto(s) asociado(s) a este proyecto</p>
                </div>
            </div>
            <div class="p-5">
                @forelse($proyecto->products as $product)
                    @foreach($product->groupProducts as $gp)
                    <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $gp->titulo }}</p>
                            <p class="text-xs text-slate-500">{{ $gp->anio_publicacion }}</p>
                        </div>
                        @php
                            $badge = match($gp->estado_revision?->value) {
                                'pendiente'   => 'bg-amber-100 text-amber-700',
                                'en_revision' => 'bg-blue-100 text-blue-700',
                                'aprobado'    => 'bg-green-100 text-green-700',
                                'rechazado'   => 'bg-red-100 text-red-700',
                                default       => 'bg-slate-100 text-slate-600',
                            };
                            $label = match($gp->estado_revision?->value) {
                                'pendiente'   => 'Pendiente',
                                'en_revision' => 'En revisión',
                                'aprobado'    => 'Aprobado',
                                'rechazado'   => 'Rechazado',
                                default       => 'Desconocido',
                            };
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                            <a href="{{ route('investigador.productos.show', $gp) }}" class="text-[#39A900] text-xs hover:underline">Ver →</a>
                        </div>
                    </div>
                    @endforeach
                @empty
                    <p class="text-sm text-slate-400 text-center py-4">Sin productos registrados para este proyecto aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Evidencias del proyecto --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Evidencias del proyecto</h3>
            </div>
            <div class="p-5 space-y-3">
                {{-- Formulario subida --}}
                <form method="POST" action="{{ route('investigador.proyectos.evidencias.store', $proyecto) }}" enctype="multipart/form-data"
                      class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Archivo(s) (PDF, imagen, Word — máx. 10 MB c/u)</label>
                        <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple
                               class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-sm file:font-medium hover:file:bg-slate-200 transition-all">
                        <p class="text-xs text-slate-400 mt-1">Usa Ctrl o Shift al seleccionar la evidencia para subir múltiples archivos a la vez.</p>
                        @error('archivos')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        @error('archivos.*')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex-shrink-0">
                        Subir
                    </button>
                </form>

                {{-- Lista de evidencias --}}
                @forelse($proyecto->projectEvidences as $evidencia)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                        <div class="min-w-0">
                            <span class="text-sm text-slate-700 truncate block">{{ $evidencia->nombre ?? basename($evidencia->archivo) }}</span>
                            <span class="text-xs text-slate-400">Subido el {{ $evidencia->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Descargar --}}
                        <a href="{{ route('investigador.proyectos.evidencias.download', $evidencia) }}"
                           class="inline-flex items-center gap-1 text-xs font-medium text-[#39A900] hover:underline"
                           title="Descargar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Descargar
                        </a>
                        {{-- Eliminar --}}
                        <form method="POST" action="{{ route('investigador.evidencias.proyecto.destroy', $evidencia) }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('¿Eliminar esta evidencia?')"
                                    class="text-slate-300 hover:text-red-500 transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                    <p class="text-sm text-slate-400">Sin evidencias cargadas aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('investigador.proyectos.index') }}"
               class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                ← Volver a proyectos
            </a>
        </div>

    </div>
</x-app-layout>
