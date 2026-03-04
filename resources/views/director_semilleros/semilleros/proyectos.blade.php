<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($semillero->projects as $proyecto)
    <div class="border border-slate-200 rounded-lg p-4 hover:border-blue-200 hover:shadow-sm transition-all bg-white flex flex-col h-full">
        <div class="flex justify-between items-start mb-3">
            <h4 class="text-sm font-bold text-slate-900 leading-tight">
                {{ $proyecto->title }}
            </h4>
            <span class="ml-3 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 shrink-0">
                {{ $proyecto->status }}
            </span>
        </div>
        
        <div class="text-xs text-slate-500 mb-4 line-clamp-3 flex-grow">
            {{ $proyecto->description ?? 'Sin descripción.' }}
        </div>
        
        <div class="flex items-center gap-3 pt-3 border-t border-slate-100 mt-auto text-xs text-slate-400">
            <div class="flex items-center gap-1" title="Fecha inicio">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                {{ $proyecto->start_date ? \Carbon\Carbon::parse($proyecto->start_date)->format('d/m/Y') : 'N/A' }}
            </div>
            <div class="flex items-center gap-1" title="Autores">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                {{ $proyecto->projectAuthors ? $proyecto->projectAuthors->count() : 0 }} Autores
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-8 text-center text-slate-500 border border-slate-100 border-dashed rounded-lg bg-slate-50">
        <p>No hay proyectos asociados a este semillero.</p>
    </div>
    @endforelse
</div>
