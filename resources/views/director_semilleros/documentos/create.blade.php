@extends('director_semilleros.layout')

@section('title', 'Subir Documento')
@section('header', 'Nuevo Documento Institucional')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('dir-sem.documentos.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Volver a documentos
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900 font-heading">Subir Archivo</h3>
            <p class="text-xs text-slate-500 mt-1">Sube documentos como normativas o guías para los semilleros.</p>
        </div>
        
        <form action="{{ route('dir-sem.documentos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="space-y-6">
                
                <!-- Nombre del Documento -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Formato de inscripción de proyecto v2"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Semillero Asociado -->
                <div>
                    <label for="semillero_id" class="block text-sm font-medium text-slate-700 mb-1.5">Semillero Asociado (Opcional)</label>
                    <div class="relative">
                        <select name="semillero_id" id="semillero_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10">
                            <option value="">Documento Institucional (Global)</option>
                            @foreach($semilleros as $semillero)
                                <option value="{{ $semillero->id }}" {{ old('semillero_id') == $semillero->id ? 'selected' : '' }}>
                                    {{ $semillero->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5">Si dejas esto en blanco, el documento no estará asociado a un semillero específico.</p>
                </div>

                <!-- Archivo -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo <span class="text-red-500">*</span></label>
                    <div class="mt-2 flex justify-center rounded-lg border border-dashed border-slate-300 px-6 py-10 hover:bg-slate-50 transition-colors bg-white relative">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                            </svg>
                            <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                <label for="archivo" class="relative cursor-pointer rounded-md bg-white font-semibold text-[#39A900] focus-within:outline-none hover:text-[#2d8500]">
                                    <span>Selecciona un archivo</span>
                                    <input id="archivo" name="archivo" type="file" class="sr-only" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                                </label>
                            </div>
                            <p class="text-xs leading-5 text-slate-500 mt-1">PDF, DOCX, XLSX hasta 10MB</p>
                            <p id="file-name" class="text-sm font-medium text-slate-800 mt-3 hidden"></p>
                        </div>
                    </div>
                    @error('archivo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('dir-sem.documentos.index') }}" 
                   class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all focus:ring-2 focus:ring-slate-200 outline-none">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all focus:ring-2 focus:ring-[#39A900]/30 outline-none flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                    Subir Documento
                </button>
            </div>
            
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('archivo').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var fileNameEl = document.getElementById('file-name');
        fileNameEl.textContent = 'Archivo seleccionado: ' + fileName;
        fileNameEl.classList.remove('hidden');
    });
</script>
@endpush
@endsection
