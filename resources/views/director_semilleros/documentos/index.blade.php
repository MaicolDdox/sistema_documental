@extends('layouts.sgd')

@section('title', 'Documentos Institucionales')
@section('header', 'Repositorio de Documentos')

@section('content')
<div x-data="{
        modalSubir: @json($errors->any() && old('_from_modal')),
        fileName: '',
        deleteOpen: false,
        deleteUrl: '',
        deleteName: ''
    }">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-sm font-semibold text-slate-800">Documentos del Centro</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de normativas, formatos y documentos compartidos de los semilleros.</p>
    </div>
    
    @can('documentos.subir')
    <button type="button" @click="modalSubir = true" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all flex items-center gap-2 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
        Subir Documento
    </button>
    @endcan
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre del Documento</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Semillero Asociado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Subido por</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documentos as $doc)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                @if(Str::endsWith($doc->url_archivo, '.pdf')) bg-red-50 text-red-500
                                @elseif(Str::endsWith($doc->url_archivo, ['.xls', '.xlsx'])) bg-green-50 text-green-600
                                @elseif(Str::endsWith($doc->url_archivo, ['.doc', '.docx'])) bg-green-50 text-[#39A900]
                                @else bg-slate-100 text-slate-500 @endif">
                                <!-- Document icon logic depending on extension -->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $doc->archivo }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ Str::upper(pathinfo($doc->url_archivo, PATHINFO_EXTENSION)) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-sm">
                        {{ $doc->seedling->nombre ?? 'Sin semillero (Institucional)' }}
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-sm">
                        {{ $doc->user->person->primer_nombre ?? '' }} {{ $doc->user->person->primer_apellido ?? $doc->user->email ?? 'Sistema' }}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-500 text-sm">
                        {{ $doc->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="relative flex items-center justify-end" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <a href="{{ Storage::url($doc->url_archivo) }}"
                                   target="_blank"
                                   @click="open = false"
                                   class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    <span>Descargar</span>
                                </a>
                                @can('documentos.eliminar_propio')
                                @if($doc->user_id === Auth::id())
                                <button type="button"
                                        @click="open = false; deleteUrl = '{{ route('dir-sem.documentos.destroy', $doc->id) }}'; deleteName = '{{ addslashes($doc->archivo) }}'; deleteOpen = true;"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79a48.11 48.11 0 013.478-.397m0 0V4.5c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 013.22 0C15.74 2.336 16.65 3.32 16.65 4.5v.893m0 0a48.108 48.108 0 013.478.397M4.772 5.79L4.5 19.5A2.25 2.25 0 006.75 21h10.5a2.25 2.25 0 002.25-2.25L19.228 5.79" />
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <p>No se han encontrado documentos subidos.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($documentos->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
        {{ $documentos->links() }}
    </div>
    @endif
</div>

    {{-- Modal: Subir Documento --}}
    @can('documentos.subir')
    <div x-show="modalSubir" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalSubir" @click.self="modalSubir = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalSubir" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#39A900]/10 text-[#39A900] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Subir documento</h3>
                            <p class="text-xs text-slate-500">Normativas, formatos o guías para los semilleros.</p>
                        </div>
                    </div>
                    <button type="button" @click="modalSubir = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('dir-sem.documentos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <input type="hidden" name="_from_modal" value="1">
                    <div class="space-y-4 mb-5">
                        <div>
                            <label for="modal_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre del documento <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="modal_nombre" value="{{ old('nombre') }}" required placeholder="Ej: Formato de inscripción v2"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('nombre') border-red-300 @enderror">
                            @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="modal_semillero_id" class="block text-sm font-medium text-slate-700 mb-1">Semillero asociado (opcional)</label>
                            <select name="semillero_id" id="modal_semillero_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="">Documento institucional (global)</option>
                                @foreach($semilleros ?? [] as $semillero)
                                    <option value="{{ $semillero->id }}" {{ old('semillero_id') == $semillero->id ? 'selected' : '' }}>{{ $semillero->nombre }}</option>
                                @endforeach
                            </select>
                            @error('semillero_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Archivo <span class="text-red-500">*</span></label>
                            <div class="mt-2 flex justify-center rounded-lg border border-dashed border-slate-300 px-4 py-8 hover:bg-slate-50 transition-colors bg-white">
                                <div class="text-center">
                                    <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd"/></svg>
                                    <label for="modal_archivo" class="mt-2 cursor-pointer rounded-md font-semibold text-[#39A900] hover:text-[#2d8500] text-sm">
                                        <span>Seleccionar archivo</span>
                                        <input id="modal_archivo" name="archivo" type="file" class="sr-only" required accept=".pdf,.doc,.docx,.xls,.xlsx" @change="fileName = $event.target.files[0]?.name || ''">
                                    </label>
                                    <p class="text-xs text-slate-500 mt-1">PDF, DOCX, XLSX hasta 10MB</p>
                                    <p x-show="fileName" x-text="'Archivo: ' + fileName" class="text-sm font-medium text-slate-700 mt-2"></p>
                                </div>
                            </div>
                            @error('archivo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="modalSubir = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            Subir Documento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal eliminar documento (estilo bonito) --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 bg-red-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l7.5-15 7.5 15h-15z" />
                        </svg>
                    </span>
                    <h2 class="text-sm font-semibold text-red-800">Eliminar documento</h2>
                </div>
                <button type="button" @click="deleteOpen = false" class="p-1.5 rounded-lg hover:bg-red-100 text-red-500" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-2">
                <p class="text-sm text-slate-700">¿Estás seguro de que deseas eliminar el documento:</p>
                <p class="text-sm font-semibold text-slate-900" x-text="deleteName"></p>
                <p class="text-xs text-slate-500 mt-1">Esta acción no se puede deshacer y eliminará el archivo del repositorio.</p>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button"
                        @click="deleteOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-white">
                    Cancelar
                </button>
                <form :action="deleteUrl" method="POST" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
