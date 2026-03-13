<x-app-layout>
    <x-slot name="header">{{ $macroproyecto ? 'Editar Macroproyecto' : 'Nuevo Macroproyecto' }}</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $macroproyecto ? 'Editar Macroproyecto' : 'Nuevo Macroproyecto' }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Define los detalles del macroproyecto en el catálogo grupal.</p>
            </div>
            <a href="{{ route('director.macroproyectos.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700 font-medium">Volver a la lista</a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <form method="POST" action="{{ $macroproyecto ? route('director.macroproyectos.update', $macroproyecto) : route('director.macroproyectos.store') }}">
                @csrf
                @if($macroproyecto) @method('PUT') @endif

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo" value="{{ old('codigo', $macroproyecto?->codigo) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm font-mono text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('codigo') border-red-300 @enderror">
                        @error('codigo') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-1.5">Identificador único del macroproyecto dentro del grupo.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Macroproyecto <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $macroproyecto?->nombre) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('nombre') border-red-300 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                        <select name="estado" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <option value="activo" {{ old('estado', $macroproyecto?->estado?->value) === 'activo' ? 'selected' : '' }}>🟢 Activo (Proyectos pueden vincularse)</option>
                            <option value="inactivo" {{ old('estado', $macroproyecto?->estado?->value) === 'inactivo' ? 'selected' : '' }}>⚪ Inactivo (Se oculta del listado en nuevos proyectos)</option>
                        </select>
                        @error('estado') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center gap-3 justify-end">
                    <a href="{{ route('director.macroproyectos.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">Cancelar</a>
                    <button type="submit" class="sgd-btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">
                        {{ $macroproyecto ? 'Guardar Cambios' : 'Crear Macroproyecto' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
