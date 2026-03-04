@extends('director_semilleros.layout')

@section('title', 'Editar Semillero')
@section('header', 'Editar: ' . $semillero->nombre)

@section('content')
<div class="max-w-3xl mx-auto">
    
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('dir-sem.semilleros.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Volver a la lista
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900 font-heading">Información del Semillero</h3>
            <p class="text-xs text-slate-500 mt-1">Modifica los datos del semillero.</p>
        </div>
        
        <form action="{{ route('dir-sem.semilleros.update', $semillero) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Semillero <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $semillero->nombre) }}" required 
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Líder -->
                <div>
                    <label for="lider_id" class="block text-sm font-medium text-slate-700 mb-1.5">Líder Asignado <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="lider_id" id="lider_id" required
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10">
                            @foreach($lideres as $lider)
                                <option value="{{ $lider->id }}" {{ old('lider_id', $semillero->leader_id) == $lider->id ? 'selected' : '' }}>
                                    {{ $lider->person->primer_nombre ?? '' }} {{ $lider->person->primer_apellido ?? '' }} ({{ $lider->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </div>
                    </div>
                    @error('lider_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción o Enfoque (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="4" 
                              class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">{{ old('descripcion', $semillero->descripccion) }}</textarea>
                    @error('descripcion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('dir-sem.semilleros.index') }}" 
                   class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all focus:ring-2 focus:ring-slate-200 outline-none">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all focus:ring-2 focus:ring-[#39A900]/30 outline-none flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Actualizar Semillero
                </button>
            </div>
            
        </form>
    </div>
</div>
@endsection
