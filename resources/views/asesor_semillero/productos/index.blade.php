<x-app-layout>
<div x-data="{ confirmDeleteId: null, showDetailId: null, editOpen: false, editUrl: null }">
    {{-- Header acciones: botón registrar --}}
    <div class="mb-4 flex justify-end">
        <a href="{{ route('asesor.productos.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
           style="background:#39A900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Registrar Producto
        </a>
    </div>
{{-- Filtro por proyecto --}}
<form method="GET" class="mb-4 flex gap-3 flex-wrap">
    <select name="proyecto" onchange="this.form.submit()" class="border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        <option value="">Todos los proyectos</option>
        @foreach($proyectos as $proy)
            <option value="{{ $proy->id }}" {{ request('proyecto') == $proy->id ? 'selected' : '' }}>{{ Str::limit($proy->nombre, 50) }}</option>
        @endforeach
    </select>
</form>

@if($productos->isEmpty())
<div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
    <p class="text-slate-500 text-sm">No hay productos registrados.</p>
        <a href="{{ route('asesor.productos.create') }}" class="mt-3 inline-block text-sm font-medium" style="color:#39A900">Registrar el primer producto →</a>
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
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
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
                        // Tomar primero el estado guardado en products; si está vacío, usar el de group_products (flujo del líder)
                        $estadoBase = $product->estado_revision;
                        if (!$estadoBase && $product->groupProducts && $product->groupProducts->isNotEmpty()) {
                            $gpEstado = $product->groupProducts->first()->estado_revision;
                            $estadoBase = $gpEstado instanceof \App\Enums\EstadoRevisionEnum ? $gpEstado->value : $gpEstado;
                        }
                        $er = $estadoBase ?: 'pendiente';
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
                        {{-- Ver (abre modal) --}}
                        <button type="button"
                           @click="showDetailId = {{ $product->id }}"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-all"
                           title="Ver producto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s2.25-6.75 9.75-6.75S21.75 12 21.75 12 19.5 18.75 12 18.75 2.25 12 2.25 12z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
                        {{-- Editar (abre modal con formulario) --}}
                        <button type="button"
                           @click="editUrl = '{{ route('asesor.productos.edit', $product->id) }}?embedded=1'; editOpen = true"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-200 hover:bg-emerald-500 hover:text-white transition-all"
                           title="Editar producto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.862 4.487" />
                            </svg>
                        </button>
                        {{-- Eliminar --}}
                        <form method="POST" action="{{ route('asesor.productos.destroy', $product->id) }}" id="delete-product-{{ $product->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    @click="confirmDeleteId = {{ $product->id }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-red-200 text-red-500 bg-red-50/60 hover:bg-red-100 transition-all"
                                    title="Eliminar producto">
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
@if($productos->hasPages())
<div class="mt-4">{{ $productos->links() }}</div>
@endif
@endif

{{-- Modal detalle producto --}}
<div x-show="showDetailId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
    <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-slate-200"
         @click.self="showDetailId = null">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-slate-100 rounded-t-2xl z-10">
            <h2 class="text-base font-semibold text-slate-900">Detalle del producto</h2>
            <button type="button" @click="showDetailId = null" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-5">
            @foreach($productos as $product)
            @php
                $sem = \Illuminate\Support\Facades\DB::table('project_seedlings')
                    ->join('seedlings','seedlings.id','=','project_seedlings.seedling_id')
                    ->where('project_seedlings.project_id', $product->project_id)
                    ->value('seedlings.nombre');
            @endphp
            <div x-show="showDetailId === {{ $product->id }}" x-cloak class="space-y-5">
                <div>
                    <h3 class="font-outfit font-bold text-xl text-slate-900 mb-1">{{ $product->nombre }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Semillero</p>
                            <p class="text-sm font-medium text-slate-700">{{ $sem ?? '—' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Proyecto</p>
                            <p class="text-sm font-medium text-slate-700">{{ $product->project?->nombre ?? '—' }}</p>
                        </div>
                        @php
                            // Mismo cálculo de estado que en la tabla principal
                            $detalleEstadoBase = $product->estado_revision;
                            if (!$detalleEstadoBase && $product->groupProducts && $product->groupProducts->isNotEmpty()) {
                                $gpDetalleEstado = $product->groupProducts->first()->estado_revision;
                                $detalleEstadoBase = $gpDetalleEstado instanceof \App\Enums\EstadoRevisionEnum ? $gpDetalleEstado->value : $gpDetalleEstado;
                            }
                            $detalleEr = $detalleEstadoBase ?: 'pendiente';
                            $detalleLabel = match($detalleEr) {
                                'aprobado'  => 'Aprobado',
                                'rechazado' => 'Rechazado',
                                'en_revision' => 'En revisión',
                                default     => 'Pendiente',
                            };
                        @endphp
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Estado de revisión</p>
                            <p class="text-sm font-medium text-slate-700">
                                {{ $detalleLabel }}
                            </p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Archivo / enlace</p>
                            <div class="flex flex-col gap-1 text-sm">
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
                                    <span class="text-slate-400">Sin archivo ni enlace registrado.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-slate-900 mb-3">Autores</h4>
                    @if($product->productAuthors->isNotEmpty())
                        <ul class="space-y-1 text-sm text-slate-700">
                            @foreach($product->productAuthors as $pa)
                                @php
                                    $p = $pa->projectAuthor?->user?->person;
                                    $nombreCompleto = $p ? trim($p->primer_nombre.' '.$p->segundo_nombre.' '.$p->primer_apellido.' '.$p->segundo_apellido) : 'Autor';
                                @endphp
                                <li class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-semibold">
                                        {{ strtoupper(substr($nombreCompleto,0,1)) }}
                                    </span>
                                    <span>{{ $nombreCompleto }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-slate-400">Este producto aún no tiene autores asociados.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modal confirmar eliminación --}}
<div x-show="confirmDeleteId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
    <div class="relative bg-slate-900 text-white rounded-2xl shadow-2xl max-w-sm w-full px-6 py-5">
        <div class="flex items-start gap-3">
            <div class="mt-0.5">
                <div class="w-8 h-8 rounded-full bg-red-500/10 border border-red-500/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5M12 15.75h.007v.008H12v-.008z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75l1.5 9A1.5 1.5 0 007.49 21h9.02a1.5 1.5 0 001.49-1.25l1.5-9M10.5 5.25h3M9 5.25A1.5 1.5 0 0110.5 3.75h3A1.5 1.5 0 0115 5.25M4.5 9.75h15" />
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold mb-1.5">
                    ¿Seguro que deseas eliminar este producto?
                </p>
                <p class="text-xs text-slate-300">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button"
                    @click="confirmDeleteId = null"
                    class="px-4 py-2.5 rounded-lg text-xs font-medium border border-slate-600 text-slate-200 hover:bg-slate-800 transition-all">
                Cancelar
            </button>
            <button type="button"
                    @click="
                        const f = document.getElementById('delete-product-' + confirmDeleteId);
                        if (f) { f.submit(); }
                        confirmDeleteId = null;
                    "
                    class="px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-red-500 hover:bg-red-600 shadow-sm transition-all">
                Aceptar
            </button>
        </div>
    </div>
</div>

{{-- Modal edición producto (iframe con formulario completo) --}}
<div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
        <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Editar producto</h2>
            <button type="button" @click="editOpen = false; editUrl = null" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="w-full h-[80vh]">
            {{-- Usamos un iframe para reutilizar el formulario de edición existente --}}
            <iframe
                :src="editUrl"
                class="w-full h-full border-0"
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>

</div>

</x-app-layout>
