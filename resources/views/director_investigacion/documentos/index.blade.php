<x-app-layout>
    <x-slot name="header">Documentos del Grupo</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Documentos del Grupo</h1>
        <p class="text-sm text-slate-500 mt-0.5">Documentos institucionales del grupo de investigación</p>
    </div>

    {{-- Subir documento --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-5">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
            <h2 class="text-sm font-semibold text-slate-900">Subir nuevo documento</h2>
        </div>
        <form method="POST" action="{{ route('director.documentos.store') }}"
              enctype="multipart/form-data"
              class="p-5 flex flex-wrap items-end gap-4">
            @csrf

            @if($errors->any())
            <div class="w-full bg-red-50 border border-red-200 rounded-lg p-3">
                @foreach($errors->all() as $e)
                    <p class="text-sm text-red-700">• {{ $e }}</p>
                @endforeach
            </div>
            @endif

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-700 mb-1.5">Nombre del documento</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       placeholder="Ej: Acta de Reunión Enero 2026"
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-700 mb-1.5">Archivo (PDF, Word, Excel)</label>
                <input type="file" name="archivo" required
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                       class="w-full text-sm text-slate-600 border border-slate-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-md file:text-xs file:font-medium file:bg-[#39A900]/10 file:text-[#39A900] file:border-0 hover:file:bg-[#39A900]/20">
            </div>
            <button type="submit" class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold shrink-0">
                Subir
            </button>
        </form>
    </div>

    {{-- Lista de documentos --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-900">Documentos del grupo</h2>
        </div>
        @forelse($documentos as $doc)
        <div class="flex items-center gap-4 px-5 py-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-slate-800 truncate">{{ $doc->nombre }}</p>
                <p class="text-xs text-slate-400">{{ $doc->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank"
                   class="text-xs text-[#39A900] hover:underline font-medium px-3 py-1.5 rounded border border-[#39A900]/30 hover:bg-[#f0fdf4] transition-all">
                    Descargar
                </a>
                @if($doc->user_id === auth()->id())
                <form method="POST" action="{{ route('director.documentos.destroy', $doc) }}"
                      onsubmit="return confirm('¿Eliminar este documento?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs text-red-500 hover:text-red-700 px-3 py-1.5 rounded border border-red-200 hover:bg-red-50 transition-all">
                        Eliminar
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-12 text-center text-slate-500">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            No hay documentos subidos aún.
        </div>
        @endforelse
        @if($documentos->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $documentos->links() }}</div>
        @endif
    </div>
</x-app-layout>
