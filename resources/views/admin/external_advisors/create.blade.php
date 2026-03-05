<x-app-layout>
    <x-slot name="header">Nuevo Asesor Externo</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.external-advisors.index') }}" class="hover:text-slate-700">Asesores Externos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Nuevo</span>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Nuevo Asesor Externo</h2>
            <p class="text-sm text-slate-500 mt-1">Registra un asesor externo. Puede o no tener cuenta en el sistema.</p>
        </div>
        <a href="{{ route('admin.external-advisors.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-2xl">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Datos del asesor</h3>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('admin.external-advisors.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="nombre_completo" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre_completo" id="nombre_completo" value="{{ old('nombre_completo') }}" required
                           class="w-full border @error('nombre_completo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('nombre_completo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                           class="w-full border @error('telefono') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('telefono')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="institucion" class="block text-sm font-medium text-slate-700 mb-1.5">Institución</label>
                    <input type="text" name="institucion" id="institucion" value="{{ old('institucion') }}"
                           class="w-full border @error('institucion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('institucion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.external-advisors.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
