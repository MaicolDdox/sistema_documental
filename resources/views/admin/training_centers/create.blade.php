<x-app-layout>
    <x-slot name="header">Nuevo Centro de Formación</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.training-centers.index') }}" class="hover:text-slate-700">Centros de Formación</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Nuevo</span>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Nuevo Centro de Formación</h2>
            <p class="text-sm text-slate-500 mt-1">Registra un centro vinculado a departamento y ciudad.</p>
        </div>
        <a href="{{ route('admin.training-centers.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-2xl">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Datos del centro</h3>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('admin.training-centers.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="codigo" class="block text-sm font-medium text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                    <input type="number" name="codigo" id="codigo" value="{{ old('codigo') }}" min="0" required
                           class="w-full border @error('codigo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-slate-700 mb-1.5">Departamento <span class="text-red-500">*</span></label>
                    <select name="department_id" id="department_id" required
                            class="w-full border @error('department_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="">Seleccionar...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->nombre }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="city_id" class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad <span class="text-red-500">*</span></label>
                    <select name="city_id" id="city_id" required
                            class="w-full border @error('city_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="">Seleccionar...</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" data-department="{{ $city->department_id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->nombre }} ({{ $city->department?->nombre }})</option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.training-centers.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
