@extends('layouts.sgd')

@section('title', 'Nuevo Líder de Semillero')
@section('header', 'Crear Líder')

@section('content')
<div class="max-w-3xl mx-auto">
    
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('dir-sem.lideres.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Volver a la lista
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-green-50 text-[#39A900] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.66-1.548c0 .12.008.239.025.358A4.5 4.5 0 014 19.235z" /></svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-900 font-heading">Datos del Nuevo Líder</h3>
                <p class="text-xs text-slate-500 mt-0.5">La contraseña temporal se generará automáticamente.</p>
            </div>
        </div>
        
        <form action="{{ route('dir-sem.lideres.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required 
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required 
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('apellido') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Documento -->
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">No. de Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" required 
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('numero_documento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="email@sena.edu.co"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Semillero Asignación (Opcional) -->
            <div class="mb-6">
                <label for="semillero_id" class="block text-sm font-medium text-slate-700 mb-1.5">Asignar a Semillero (Opcional)</label>
                <p class="text-xs text-slate-500 mb-2">Solo aparecen semilleros <span class="font-medium">sin líder asignado</span> en tu centro.</p>
                <div class="relative">
                    <select name="semillero_id" id="semillero_id"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all appearance-none pr-10">
                        <option value="">Selecciona un semillero (o asigna después)...</option>
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
                @error('semillero_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            @canany(['usuarios.crear_lider_semillero', 'usuarios.asignar_credenciales'])
            <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg flex items-start gap-3">
                <div class="mt-0.5">
                    <input type="checkbox" name="enviar_credenciales" id="enviar_credenciales" value="1"
                           {{ old('enviar_credenciales', false) ? 'checked' : '' }}
                           class="w-4 h-4 text-[#39A900] bg-white border-slate-300 rounded focus:ring-2 focus:ring-[#39A900]">
                </div>
                <div class="text-sm">
                    <label for="enviar_credenciales" class="font-medium text-slate-800 cursor-pointer">Enviar credenciales por correo electrónico</label>
                    <p class="text-slate-500 mt-0.5">La contraseña temporal siempre se genera para entregarla manualmente al líder. Si activas esta opción, además se enviará por correo.</p>
                </div>
            </div>
            @endcanany

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('dir-sem.lideres.index') }}" 
                   class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all focus:ring-2 focus:ring-slate-200 outline-none">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all focus:ring-2 focus:ring-[#39A900]/30 outline-none flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Registrar Líder
                </button>
            </div>
            
        </form>
    </div>
</div>
@endsection
