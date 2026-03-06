<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    @forelse($semillero->seedlingFiles as $archivo)
    <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-slate-300 transition-colors flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0
            @if(Str::endsWith($archivo->url_archivo, '.pdf')) bg-red-50 text-red-500
            @elseif(Str::endsWith($archivo->url_archivo, ['.xls', '.xlsx'])) bg-green-50 text-green-600
            @elseif(Str::endsWith($archivo->url_archivo, ['.doc', '.docx'])) bg-green-50 text-[#39A900]
            @else bg-slate-100 text-slate-500 @endif
        ">
            @if(Str::endsWith($archivo->url_archivo, '.pdf'))
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            @else
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            @endif
        </div>
        
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-semibold text-slate-800 truncate" title="{{ $archivo->archivo }}">{{ $archivo->archivo }}</h4>
            <p class="text-xs text-slate-400 mt-1">{{ $archivo->created_at->format('d/m/Y - h:i A') }}</p>
        </div>
        
        <a href="{{ Storage::url($archivo->url_archivo) }}" target="_blank" class="p-2 text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors shrink-0" title="Descargar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
        </a>
    </div>
    @empty
    <div class="col-span-full py-8 text-center text-slate-500 border border-slate-100 border-dashed rounded-lg bg-slate-50">
        <p>No se han registrado evidencias o documentos en el semillero.</p>
    </div>
    @endforelse
</div>
