@extends('layouts.sgd')

@section('title', 'Nuevo Co-investigador SDI')
@section('header', 'Crear Co-investigador SDI')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="mb-5">
        <a href="{{ route('dir-sem.co-investigadores.index') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Volver a la lista
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900">Datos del nuevo Co-investigador SDI</h3>
            <p class="text-xs text-slate-500 mt-0.5">Después de crearlo, el Líder de Proyecto podrá vincularlo a un proyecto de tu centro.</p>
        </div>

        <form action="{{ route('dir-sem.co-investigadores.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('apellido') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="tipo_documento" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Documento <span class="text-red-500">*</span></label>
                    <select name="tipo_documento" id="tipo_documento" required
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="">Selecciona un tipo...</option>
                        @foreach(\App\Enums\TipoDocumentoEnum::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_documento') === $tipo->value ? 'selected' : '' }}>{{ ucfirst($tipo->value) }}</option>
                        @endforeach
                    </select>
                    @error('tipo_documento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">No. de Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('numero_documento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña Temporal <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    <p class="text-xs text-slate-500 mt-1">Mínimo 8 caracteres.</p>
                    @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg flex items-start gap-3">
                <input type="checkbox" name="enviar_credenciales" id="enviar_credenciales" value="1" {{ old('enviar_credenciales') ? 'checked' : '' }}
                       class="mt-1 w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-2 focus:ring-[#39A900]">
                <label for="enviar_credenciales" class="text-sm text-slate-700">Enviar credenciales por correo al co-investigador.</label>
            </div>

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('dir-sem.co-investigadores.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Cancelar</a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all">Registrar Co-investigador</button>
            </div>
        </form>
    </div>
</div>
@endsection
