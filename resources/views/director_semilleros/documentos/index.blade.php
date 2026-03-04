@extends('director_semilleros.layout')

@section('title', 'Documentos Institucionales')
@section('header', 'Repositorio de Documentos')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-sm font-semibold text-slate-800">Documentos del Centro</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de normativas, formatos y documentos compartidos de los semilleros.</p>
    </div>
    
    @can('documentos.subir')
    <a href="{{ route('dir-sem.documentos.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all flex items-center gap-2 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
        Subir Documento
    </a>
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
                                @elseif(Str::endsWith($doc->url_archivo, ['.doc', '.docx'])) bg-blue-50 text-blue-600
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
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ Storage::url($doc->url_archivo) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Descargar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            </a>
                            
                            @can('documentos.eliminar_propio')
                            @if($doc->user_id === Auth::id())
                            <form action="{{ route('dir-sem.documentos.destroy', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este documento de forma permanente?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
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
@endsection
