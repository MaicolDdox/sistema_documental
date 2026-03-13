<x-app-layout>
<div class="max-w-2xl" x-data="{
    fileName: null,
    handleFile(e) {
        const f = e.target.files[0];
        this.fileName = f ? f.name : null;
    }
}">
    <form method="POST" action="{{ route('asesor.productos.update', $product->id) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        {{-- TARJETA 1: Proyecto --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4">
                    <svg class="w-5 h-5" style="color:#39A900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Proyecto asociado</h3>
                    <p class="text-xs text-slate-400">Proyecto al que pertenece este producto</p>
                </div>
            </div>
            <select name="project_id"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all bg-slate-50 @error('project_id') border-red-400 @enderror">
                <option value="">Seleccionar proyecto...</option>
                @foreach($proyectos as $proy)
                    <option value="{{ $proy->id }}" {{ old('project_id', $product->project_id) == $proy->id ? 'selected' : '' }}>
                        {{ $proy->nombre }}
                    </option>
                @endforeach
            </select>
            @error('project_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- TARJETA 2: Nombre --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eff6ff">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Nombre del producto</h3>
                </div>
            </div>
            <input type="text" name="nombre" value="{{ old('nombre', $product->nombre) }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all bg-slate-50 @error('nombre') border-red-400 @enderror">
            @error('nombre') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- TARJETA 3: Archivo / Enlace --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fdf4ff">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Archivo del producto</h3>
                    <p class="text-xs text-slate-400">Actualiza el archivo o enlace si es necesario</p>
                </div>
            </div>

            {{-- Archivo actual --}}
            @if($product->archivo)
            <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg border border-blue-100 text-sm text-blue-700">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Archivo actual:
                <a href="{{ Storage::url($product->archivo) }}" target="_blank" class="font-medium underline">Ver archivo</a>
                <span class="text-blue-500 text-xs">(sube uno nuevo para reemplazar)</span>
            </div>
            @endif

            {{-- Subir archivo --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700">Subir archivo</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Opcional</span>
                </div>
                <label class="block cursor-pointer">
                    <div class="border-2 border-dashed rounded-xl p-5 text-center transition-all hover:border-[#39A900] hover:bg-green-50/30"
                         :class="fileName ? 'border-[#39A900] bg-green-50/20' : 'border-slate-200'">
                        <div x-show="!fileName">
                            <svg class="w-7 h-7 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <p class="text-sm text-slate-500">Haz clic o arrastra el archivo aquí</p>
                            <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, PowerPoint — máx. 20 MB</p>
                        </div>
                        <div x-show="fileName" class="flex items-center justify-center gap-3">
                            <svg class="w-6 h-6 flex-shrink-0" style="color:#39A900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-slate-800" x-text="fileName"></p>
                                <p class="text-xs" style="color:#39A900">Nuevo archivo seleccionado ✓</p>
                            </div>
                        </div>
                    </div>
                    <input type="file" name="archivo" class="hidden" accept=".pdf,.docx,.xlsx,.pptx"
                           @change="handleFile($event)">
                </label>
                @error('archivo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Divisor --}}
            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-slate-100"></div>
                <span class="text-xs text-slate-400 font-medium">O TAMBIÉN</span>
                <div class="flex-1 h-px bg-slate-100"></div>
            </div>

            {{-- URL enlace externo --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700">Enlace externo</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Opcional</span>
                </div>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    <input type="url" name="url_repositorio" value="{{ old('url_repositorio', $product->url_repositorio) }}"
                           placeholder="https://drive.google.com/... · OneDrive · GitHub · repositorio institucional"
                           class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all bg-slate-50 @error('url_repositorio') border-red-400 @enderror">
                </div>
                <p class="text-xs text-slate-400 mt-1.5">Google Drive, OneDrive, Dropbox, GitHub, repositorio institucional, etc.</p>
                @error('url_repositorio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-md"
                    style="background:#39A900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Guardar cambios
            </button>
            <a href="{{ route('asesor.productos.show', $product->id) }}"
               class="px-6 py-3 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">
                Cancelar
            </a>
        </div>
    </form>
</div>
</x-app-layout>
