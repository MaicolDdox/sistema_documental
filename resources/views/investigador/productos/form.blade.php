<x-app-layout>
    <x-slot name="header">{{ $groupProduct ? 'Corregir Producto' : 'Registrar Producto' }}</x-slot>

    {{-- Si el formulario está embebido en un modal (iframe) y la operación fue exitosa,
         recargamos la ventana padre para cerrar el modal y actualizar la lista. --}}
    @if(request()->boolean('embedded') && session('success'))
        <script>
            if (window.parent && window.parent !== window) {
                window.parent.location.reload();
            }
        </script>
    @endif

    <div class="max-w-3xl">

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @if(isset($productBase) && $productBase)
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-800">Formalizando producto de semillero</h4>
                    <p class="text-sm text-blue-700 mt-0.5">La evidencia documental principal ya fue subida por el Semillero y está adjunta a este producto.</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm space-y-1">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        {{-- Alerta si es corrección --}}
        @if($groupProduct && $groupProduct->observaciones_revision)
        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h4 class="text-sm font-semibold text-amber-800 mb-1">Observaciones del Director</h4>
            <p class="text-sm text-amber-700">{{ $groupProduct->observaciones_revision }}</p>
        </div>
        @endif

        <form method="POST"
              action="{{ $groupProduct ? route('investigador.productos.update', $groupProduct) : route('investigador.productos.store') }}">
            @csrf
            @if($groupProduct) @method('PUT') @endif

            {{-- Datos básicos --}}
            <div class="bg-white rounded-xl border border-slate-200 mb-4">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Datos del producto</h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Proyecto (solo en creación) --}}
                    @if(isset($productBase) && $productBase)
                        <input type="hidden" name="product_base_id" value="{{ $productBase->id }}">
                        <input type="hidden" name="project_id" value="{{ $productBase->project_id }}">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Proyecto asociado <span class="text-red-500">*</span></label>
                            <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600 font-medium">
                                {{ $productBase->project?->nombre ?? 'N/A' }}
                            </div>
                        </div>
                    @elseif(!isset($groupProduct) || !$groupProduct)
                    <div x-data="autoresLoader()" x-init="init()">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Proyecto asociado <span class="text-red-500">*</span></label>
                        <select name="project_id" x-model="proyectoId" @change="cargarAutores($event.target.value)"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all" required>
                            <option value="">Selecciona un proyecto...</option>
                            @foreach($proyectos as $proyecto)
                                <option value="{{ $proyecto->id }}" data-url="{{ route('investigador.proyectos.autores', $proyecto) }}">
                                    {{ $proyecto->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Solo aparecen tus proyectos. Los autores del producto deben ser autores del proyecto.</p>

                        {{-- Autores dinámicos --}}
                        <div x-show="autores.length > 0" x-transition class="mt-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Autores del producto</label>
                            <div class="space-y-2">
                                <template x-for="autor in autores" :key="autor.id">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :name="'autores[]'" :value="autor.id"
                                               class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]">
                                        <span class="text-sm text-slate-700" x-text="autor.nombre"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    @endunless

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Título del producto <span class="text-red-500">*</span></label>
                        <input type="text" name="titulo" value="{{ old('titulo', $groupProduct?->titulo ?? ($productBase?->nombre ?? '')) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                        <textarea name="descripccion" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">{{ old('descripccion', $groupProduct?->descripccion) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Año de publicación <span class="text-red-500">*</span></label>
                            <input type="number" name="anio_publicacion" value="{{ old('anio_publicacion', $groupProduct?->anio_publicacion ?? now()->year) }}"
                                   min="2000" max="{{ now()->year + 2 }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                                   required>
                        </div>
                        @if(!isset($groupProduct) || !$groupProduct)
                        <div class="col-span-2 bg-slate-50 border border-slate-100 p-4 rounded-xl mt-2" x-data="{ tipoOrigen: '{{ old('tipo_proyecto_origen', isset($productBase) && $productBase ? 'SEMILLEROS' : '') }}' }">
                            <h4 class="text-sm font-semibold text-slate-800 mb-3">Origen del Producto</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de proyecto de origen <span class="text-red-500">*</span></label>
                                    <select name="tipo_proyecto_origen" x-model="tipoOrigen"
                                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all" required>
                                        <option value="">Selecciona...</option>
                                        @foreach(\App\Enums\TipoProyectoOrigenEnum::cases() as $tipo)
                                            <option value="{{ $tipo->value }}">{{ $tipo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- Opción "Otro" --}}
                                <div x-show="tipoOrigen === 'otro'" x-transition class="col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">¿Cuál? <span class="text-red-500">*</span></label>
                                    <input type="text" name="campo_otro" value="{{ old('campo_otro') }}"
                                           :required="tipoOrigen === 'otro'"
                                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                </div>

                                {{-- Código de proyecto SENNOVA o externo --}}
                                <div x-show="tipoOrigen && tipoOrigen !== 'formativo_sena' && tipoOrigen !== 'otro'" x-transition class="col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Código del proyecto origen <span class="text-xs text-slate-400 font-normal">(Opcional si aplica)</span></label>
                                    <input type="text" name="codigo_proyecto_origen" value="{{ old('codigo_proyecto_origen') }}"
                                           placeholder="Ej: SGPS-1234"
                                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                </div>
                                
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del programa de formación que impacta <span class="text-xs text-slate-400 font-normal">(Opcional)</span></label>
                                    <input type="text" name="nombre_programa_formacion_impacto" value="{{ old('nombre_programa_formacion_impacto') }}"
                                           placeholder="Ej: Análisis y Desarrollo de Software"
                                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clasificación Minciencias --}}
            <div class="bg-white rounded-xl border border-slate-200 mb-4">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Clasificación Minciencias</h3>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipología Minciencias</label>
                        <select name="minciencias_typology_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($tipologias as $t)
                                <option value="{{ $t->id }}" {{ old('minciencias_typology_id', $groupProduct?->minciencias_typology_id) == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subcategoría Minciencias</label>
                        <select name="minciencias_subcategory_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($subcategorias as $s)
                                <option value="{{ $s->id }}" {{ old('minciencias_subcategory_id', $groupProduct?->minciencias_subcategory_id) == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Gran área del conocimiento</label>
                        <select name="knowledge_grand_area_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($grandesAreas as $ga)
                                <option value="{{ $ga->id }}" {{ old('knowledge_grand_area_id', $groupProduct?->knowledge_grand_area_id) == $ga->id ? 'selected' : '' }}>{{ $ga->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Área del conocimiento</label>
                        <select name="knowledge_area_id"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="">Selecciona...</option>
                            @foreach($areasConocimiento as $ac)
                                <option value="{{ $ac->id }}" {{ old('knowledge_area_id', $groupProduct?->knowledge_area_id) == $ac->id ? 'selected' : '' }}>{{ $ac->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Repositorio --}}
            <div class="bg-white rounded-xl border border-slate-200 mb-4" x-data="{ tieneRepo: {{ old('tiene_repositorio', $groupProduct?->tiene_repositorio ?? (isset($productBase) && $productBase?->url_repositorio ? true : false)) ? 'true' : 'false' }} }">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Repositorio y publicación</h3>
                </div>
                <div class="p-5 space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="tiene_repositorio" value="1" x-model="tieneRepo"
                               class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]"
                               {{ old('tiene_repositorio', $groupProduct?->tiene_repositorio ?? (isset($productBase) && $productBase?->url_repositorio ? true : false)) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">El producto tiene URL de repositorio</span>
                    </label>
                    <div x-show="tieneRepo" x-transition>
                        <input type="url" name="url_repositorio" value="{{ old('url_repositorio', $groupProduct?->url_repositorio ?? ($productBase?->url_repositorio ?? '')) }}"
                               placeholder="https://repositorio.sena.edu.co/..."
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="autoriza_datos" value="1"
                               class="w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-[#39A900]"
                               {{ old('autoriza_datos', $groupProduct?->autoriza_datos) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Autorizo el uso de datos del producto para informes del grupo</span>
                    </label>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    {{ $groupProduct ? 'Guardar corrección' : 'Registrar producto' }}
                </button>
                <a href="{{ route('investigador.productos.index') }}"
                   class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function autoresLoader() {
        return {
            proyectoId: '',
            autores: [],
            init() {},
            async cargarAutores(proyectoId) {
                if (!proyectoId) { this.autores = []; return; }
                const select = document.querySelector('[name="project_id"]');
                const opt = select.querySelector(`option[value="${proyectoId}"]`);
                const url = opt ? opt.dataset.url : null;
                if (!url) return;
                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.autores = await res.json();
                } catch(e) { this.autores = []; }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
