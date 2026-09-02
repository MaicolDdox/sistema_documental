@extends('layouts.sgd')

@section('title', 'Aprendices')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Aprendices — {{ $proyecto->nombre }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Registrados como datos de trazabilidad, no como usuarios del sistema.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <h2 class="text-base font-semibold text-slate-900 mb-4">Registrar aprendiz</h2>
    <form action="{{ route('lider-proyecto.aprendices.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
            <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            @error('nombre_completo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de documento <span class="text-red-500">*</span></label>
            <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            @error('numero_documento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ficha <span class="text-red-500">*</span></label>
            <input type="text" name="ficha" value="{{ old('ficha') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            @error('ficha') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            @error('telefono') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Programa de Formación <span class="text-red-500">*</span></label>
            <select name="training_program_id" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                <option value="">Selecciona...</option>
                @foreach($trainingPrograms as $tp)
                    <option value="{{ $tp->id }}" {{ (string) old('training_program_id') === (string) $tp->id ? 'selected' : '' }}>{{ $tp->nombre }}</option>
                @endforeach
            </select>
            @error('training_program_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm">Registrar aprendiz</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Nombre completo</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Documento</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Ficha</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Teléfono</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Correo</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500">Programa de Formación</th>
                    <th class="text-right px-4 py-3 font-medium text-slate-500">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aprendices as $a)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-4 py-2"><input form="form-aprendiz-{{ $a->id }}" type="text" name="nombre_completo" value="{{ $a->nombre_completo }}" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm"></td>
                    <td class="px-4 py-2"><input form="form-aprendiz-{{ $a->id }}" type="text" name="numero_documento" value="{{ $a->numero_documento }}" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm"></td>
                    <td class="px-4 py-2"><input form="form-aprendiz-{{ $a->id }}" type="text" name="ficha" value="{{ $a->ficha }}" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm"></td>
                    <td class="px-4 py-2"><input form="form-aprendiz-{{ $a->id }}" type="text" name="telefono" value="{{ $a->telefono }}" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm"></td>
                    <td class="px-4 py-2"><input form="form-aprendiz-{{ $a->id }}" type="email" name="email" value="{{ $a->email }}" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm"></td>
                    <td class="px-4 py-2">
                        <select form="form-aprendiz-{{ $a->id }}" name="training_program_id" class="w-full border border-transparent hover:border-slate-200 focus:border-[#39A900] rounded px-2 py-1 text-sm bg-white">
                            @foreach($trainingPrograms as $tp)
                                <option value="{{ $tp->id }}" {{ (int) $a->training_program_id === $tp->id ? 'selected' : '' }}>{{ $tp->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <form id="form-aprendiz-{{ $a->id }}" action="{{ route('lider-proyecto.aprendices.update', $a) }}" method="POST" class="inline">
                            @csrf @method('PUT')
                        </form>
                        <button type="submit" form="form-aprendiz-{{ $a->id }}" title="Editar" class="inline-flex items-center justify-center w-7 h-7 rounded text-[#39A900] hover:bg-green-50 mr-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zM19.5 7.125"/></svg>
                        </button>
                        <form action="{{ route('lider-proyecto.aprendices.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este aprendiz del proyecto?');">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar" class="inline-flex items-center justify-center w-7 h-7 rounded text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79a48.11 48.11 0 013.478-.397m0 0V4.5c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 013.22 0C15.74 2.336 16.65 3.32 16.65 4.5v.893m0 0a48.108 48.108 0 013.478.397M4.772 5.79L4.5 19.5A2.25 2.25 0 006.75 21h10.5a2.25 2.25 0 002.25-2.25L19.228 5.79"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500 text-sm">Aún no has registrado aprendices en este proyecto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
