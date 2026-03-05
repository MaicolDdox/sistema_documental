@extends('director_semilleros.layout')

@section('title', 'Editar Líder')
@section('header', '')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <a href="{{ route('dir-sem.lideres.index') }}" class="hover:text-[#39A900]">Líderes de Semillero</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Editar</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-6">Editar Líder</h2>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('dir-sem.lideres.update', $lider) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="primer_nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_nombre" id="primer_nombre" value="{{ old('primer_nombre', $lider->person?->primer_nombre) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('primer_nombre') border-red-300 @enderror">
                        @error('primer_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="primer_apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                        <input type="text" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido', $lider->person?->primer_apellido) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('primer_apellido') border-red-300 @enderror">
                        @error('primer_apellido') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $lider->email) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('email') border-red-300 @enderror">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Número de documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $lider->numero_documento) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('numero_documento') border-red-300 @enderror">
                    @error('numero_documento') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="estado" class="block text-sm font-medium text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                    <select name="estado" id="estado" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('estado') border-red-300 @enderror">
                        <option value="activo" {{ old('estado', $lider->estado?->value) === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $lider->estado?->value) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('estado') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-8 pt-5 border-t border-slate-100 flex gap-3">
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm">
                    Guardar cambios
                </button>
                <a href="{{ route('dir-sem.lideres.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
