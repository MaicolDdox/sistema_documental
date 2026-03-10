@extends('asesor_semillero.layout')

@section('title', 'Registrar Producto')
@section('header', 'Registrar Nuevo Producto')

@section('content')
<div class="max-w-2xl" x-data="{
    semilleroId: '{{ old('semillero_id', $semilleroSeleccionado ?? '') }}',
    proyectoId: '{{ old('project_id', $proyecto_id ?? '') }}',
    proyectos: [],
    autores: [],
    autoresSeleccionados: [],
    fileName: null,
    loadingProyectos: false,
    loadingAutores: false,

    init() {
        if (this.semilleroId) this.fetchProyectos();
    },

    fetchProyectos() {
        if (!this.semilleroId) { this.proyectos = []; this.autores = []; return; }
        this.loadingProyectos = true;
        fetch(`/asesor-semillero/api/semillero/${this.semilleroId}/proyectos`)
            .then(r => r.json())
            .then(data => { this.proyectos = data; this.loadingProyectos = false; })
            .catch(() => { this.loadingProyectos = false; });
    },

    fetchAutores() {
        if (!this.proyectoId) { this.autores = []; this.autoresSeleccionados = []; return; }
        this.loadingAutores = true;
        fetch(`/asesor-semillero/api/proyecto/${this.proyectoId}/autores`)
            .then(r => r.json())
            .then(data => { this.autores = data; this.loadingAutores = false; })
            .catch(() => { this.loadingAutores = false; });
    },

    toggleAutor(id) {
        const idx = this.autoresSeleccionados.indexOf(id);
        if (idx === -1) this.autoresSeleccionados.push(id);
        else this.autoresSeleccionados.splice(idx, 1);
    },

    isSelected(id) { return this.autoresSeleccionados.includes(id); },

    handleFile(e) {
        const f = e.target.files[0];
        this.fileName = f ? f.name : null;
    }
}" x-cloak>

    {{-- Indicador de pasos --}}
    <div class="flex items-center gap-3 mb-6">
        @foreach([['1','Semillero'], ['2','Proyecto'], ['3','Nombre'], ['4','Autores'], ['5','Archivo']] as [$n,$label])
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#39A900">{{ $n }}</div>
            <span class="text-xs font-medium text-slate-600 hidden sm:block">{{ $label }}</span>
        </div>
        @if(!$loop->last) <div class="flex-1 h-px bg-slate-200"></div> @endif
        @endforeach
    </div>

    <form method="POST" action="{{ route('asesor.productos.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- TARJETA 1: Semillero --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-green-50">
                    <svg class="w-5 h-5" style="color:#39A900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Semillero</h3>
                    <p class="text-xs text-slate-400">¿A qué semillero pertenece el producto?</p>
                </div>
            </div>
            <select name="semillero_id" x-model="semilleroId" @change="fetchProyectos(); proyectoId = ''; autores = [];"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 bg-slate-50 transition-all @error('semillero_id') border-red-400 @enderror">
                <option value="">Seleccionar semillero...</option>
                @foreach($semilleros as $sem)
                    <option value="{{ $sem->id }}" {{ old('semillero_id', $semilleroSeleccionado) == $sem->id ? 'selected' : '' }}>
                        {{ $sem->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TARJETA 2: Proyecto --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Proyecto</h3>
                    <p class="text-xs text-slate-400">Solo muestra proyectos del semillero seleccionado</p>
                </div>
            </div>

            {{-- Estado cargando --}}
            <div x-show="loadingProyectos" class="flex items-center gap-2 text-sm text-slate-500 py-3">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando proyectos...
            </div>

            <div x-show="!loadingProyectos">
                <template x-if="!semilleroId">
                    <p class="text-sm text-slate-400 py-2">↑ Selecciona primero un semillero</p>
                </template>
                <template x-if="semilleroId && proyectos.length === 0">
                    <p class="text-sm text-amber-600 py-2">Este semillero no tiene proyectos registrados</p>
                </template>
                <template x-if="semilleroId && proyectos.length > 0">
                    <select name="project_id" x-model="proyectoId" @change="fetchAutores(); autoresSeleccionados = [];"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 bg-slate-50 transition-all @error('project_id') border-red-400 @enderror">
                        <option value="">Seleccionar proyecto...</option>
                        <template x-for="p in proyectos" :key="p.id">
                            <option :value="p.id" :selected="proyectoId == p.id" x-text="p.nombre"></option>
                        </template>
                    </select>
                </template>
            </div>
            @error('project_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- TARJETA 3: Nombre --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fdf4ff">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Nombre del producto</h3>
                    <p class="text-xs text-slate-400">Título oficial del producto de investigación</p>
                </div>
            </div>
            <input type="text" name="nombre" value="{{ old('nombre') }}"
                   placeholder="Ej. Artículo científico sobre redes neuronales..."
                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 bg-slate-50 transition-all @error('nombre') border-red-400 @enderror">
            @error('nombre') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- TARJETA 4: Autores --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Autores del producto</h3>
                    <p class="text-xs text-slate-400">Selecciona los integrantes del proyecto que son autores</p>
                </div>
            </div>

            <template x-if="!proyectoId">
                <p class="text-sm text-slate-400 py-2">↑ Selecciona un proyecto para ver sus integrantes</p>
            </template>

            <div x-show="loadingAutores" class="flex items-center gap-2 text-sm text-slate-500 py-3">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando autores...
            </div>

            <template x-if="proyectoId && !loadingAutores && autores.length === 0">
                <p class="text-sm text-amber-600 py-2">Este proyecto no tiene integrantes registrados</p>
            </template>

            <div x-show="proyectoId && !loadingAutores && autores.length > 0" class="space-y-2">
                <template x-for="autor in autores" :key="autor.id">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all hover:bg-slate-50"
                           :class="isSelected(autor.id) ? 'border-[#39A900] bg-green-50/30' : 'border-slate-200'">
                        <input type="checkbox" :name="`autores[]`" :value="autor.id"
                               @change="toggleAutor(autor.id)"
                               :checked="isSelected(autor.id)"
                               class="rounded w-4 h-4" style="accent-color:#39A900">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                                 style="background:#0a1628" x-text="autor.nombre.charAt(0)"></div>
                            <span class="text-sm font-medium text-slate-800" x-text="autor.nombre"></span>
                        </div>
                    </label>
                </template>
            </div>
            @error('autores') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- TARJETA 5: Archivo del producto --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-slate-100">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Archivo del producto</h3>
                    <p class="text-xs text-slate-400">Al menos uno de los dos es requerido</p>
                </div>
            </div>

            {{-- Upload zone --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700">Subir archivo</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Opcional</span>
                </div>
                <label class="block cursor-pointer">
                    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all hover:border-[#39A900] hover:bg-green-50/30"
                         :class="fileName ? 'border-[#39A900] bg-green-50/20' : 'border-slate-200'">
                        <div x-show="!fileName">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <p class="text-sm text-slate-500">Haz clic o arrastra el archivo aquí</p>
                            <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, PowerPoint — máx. 20 MB</p>
                        </div>
                        <div x-show="fileName" class="flex items-center justify-center gap-3">
                            <svg class="w-7 h-7 flex-shrink-0" style="color:#39A900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-slate-800" x-text="fileName"></p>
                                <p class="text-xs" style="color:#39A900">Archivo seleccionado ✓</p>
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

            {{-- Enlace externo --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700">Enlace externo</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Opcional</span>
                </div>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    <input type="url" name="url_repositorio" value="{{ old('url_repositorio') }}"
                           placeholder="https://drive.google.com/ · OneDrive · GitHub · repositorio institucional"
                           class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 bg-slate-50 transition-all @error('url_repositorio') border-red-400 @enderror">
                </div>
                <p class="text-xs text-slate-400 mt-1.5">Google Drive, OneDrive, Dropbox, GitHub, repositorio institucional, etc.</p>
                @error('url_repositorio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Aviso estado pendiente --}}
        <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 text-xs text-amber-700">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            El producto queda registrado en estado <strong class="mx-0.5">Activo</strong>. Las evidencias se podrán agregar desde el detalle del producto.
        </div>

        {{-- Botones --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-md"
                    style="background:#39A900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Registrar Producto
            </button>
            <a href="{{ route('asesor.productos.index') }}"
               class="px-6 py-3 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
