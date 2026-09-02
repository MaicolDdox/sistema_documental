<div x-data="{ subirOpen: false, deleteOpen: false, deleteUrl: '', deleteName: '' }">
    <div class="flex items-center justify-between mb-4">
        <p class="text-xs text-slate-500">Documentos subidos específicamente para este semillero.</p>
        @can('documentos.subir')
        <button type="button" @click="subirOpen = true" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            Subir Documento
        </button>
        @endcan
    </div>

    <div class="overflow-x-auto border border-slate-200 rounded-lg">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Subido por</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documentos as $doc)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-slate-800">{{ $doc->archivo }}</p>
                        <p class="text-xs text-slate-400">{{ \Illuminate\Support\Str::upper(pathinfo($doc->url_archivo, PATHINFO_EXTENSION)) }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-sm">{{ $doc->user?->person?->nombre_completo ?? $doc->user?->email ?? 'Sistema' }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 text-sm">{{ $doc->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('dir-sem.documentos.descargar', $doc) }}" class="text-[#39A900] hover:underline text-xs font-medium mr-3">Descargar</a>
                        @can('documentos.eliminar_propio')
                        @if($doc->user_id === \Illuminate\Support\Facades\Auth::id())
                        <button type="button" @click="deleteUrl = '{{ route('dir-sem.documentos.destroy', $doc->id) }}'; deleteName = '{{ addslashes($doc->archivo) }}'; deleteOpen = true;" class="text-red-600 hover:underline text-xs font-medium">
                            Eliminar
                        </button>
                        @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay documentos subidos para este semillero.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal: Subir documento --}}
    @can('documentos.subir')
    <div x-show="subirOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="subirOpen" @click.self="subirOpen = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="subirOpen" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-slate-900">Subir documento — {{ $semillero->nombre }}</h3>
                    <button type="button" @click="subirOpen = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('dir-sem.documentos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <input type="hidden" name="semillero_id" value="{{ $semillero->id }}">
                    <div class="space-y-4 mb-5">
                        <div>
                            <label for="doc_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre del documento <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="doc_nombre" required placeholder="Ej: Acta de reunión mensual"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Archivo <span class="text-red-500">*</span></label>
                            <input type="file" name="archivo" required accept=".pdf,.doc,.docx,.xls,.xlsx"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700">
                            <p class="text-xs text-slate-500 mt-1">PDF, DOCX, XLSX hasta 10MB</p>
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="subirOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Subir Documento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal: eliminar documento --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 bg-red-50">
                <h2 class="text-sm font-semibold text-red-800">Eliminar documento</h2>
            </div>
            <div class="px-6 py-4 space-y-2">
                <p class="text-sm text-slate-700">¿Estás seguro de que deseas eliminar el documento:</p>
                <p class="text-sm font-semibold text-slate-900" x-text="deleteName"></p>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" @click="deleteOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-white">Cancelar</button>
                <form :action="deleteUrl" method="POST" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
