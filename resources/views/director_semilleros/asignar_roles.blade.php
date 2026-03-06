@extends('layouts.sgd')

@section('title', 'Asignación de Roles')
@section('header', 'Asignación de Roles')

@section('content')
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Usuarios</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Asignar Roles</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-1">Asignación de Roles</h2>
<p class="text-sm text-slate-500 mb-6">Vista reutilizada del administrador — opciones filtradas por @@can del rol actual.</p>

@if(session('success'))
<div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Formulario: Asignar rol a usuario --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Asignar Rol a Usuario</h3>
            <form method="POST" action="{{ route('dir-sem.asignar-roles.store') }}" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-slate-700 mb-1.5">Usuario sin rol asignado</label>
                        <select name="user_id" id="user_id" required
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all @error('user_id') border-red-300 @enderror">
                            <option value="">Seleccionar usuario...</option>
                            @foreach($usuarios as $u)
                                @php
                                    $nombre = $u->person ? $u->person->nombre_completo : $u->email;
                                    $cedula = $u->numero_documento ?? '';
                                    $label = $cedula ? "{$cedula} ({$nombre})" : $nombre;
                                @endphp
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400" aria-hidden="true">→</span>
                        <div class="flex-1">
                            <label for="rol" class="block text-sm font-medium text-slate-700 mb-1.5">Rol a asignar </label>
                            <select name="rol" id="rol" required
                                    class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all @error('rol') border-red-300 @enderror">
                                <option value="">Seleccionar rol...</option>
                                @foreach($rolesAsignables as $r)
                                    <option value="{{ $r->name }}" {{ old('rol', 'lider_semillero') == $r->name ? 'selected' : '' }}>
                                        {{ $r->label }} ✔
                                    </option>
                                @endforeach
                            </select>
                            @error('rol')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('dir-sem.dashboard') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-colors shadow-sm">
                        Guardar asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel: Roles que puedo asignar --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Roles que puedo asignar</h3>
            <ul class="space-y-3">
                <li class="flex items-center justify-between gap-2">
                    <span class="text-sm text-slate-800">Líder de Semillero</span>
                    @can('usuarios.crear_lider_semillero')
                        <span class="text-green-600 font-medium text-sm">✔</span>
                    @else
                        <span class="text-slate-400 text-xs">sin acceso</span>
                    @endcan
                </li>
                <li class="flex items-center justify-between gap-2 text-slate-400">
                    <span class="text-sm">Director de Grupo</span>
                    <span class="text-xs">sin acceso</span>
                </li>
                <li class="flex items-center justify-between gap-2 text-slate-400">
                    <span class="text-sm">Investigador Asociado</span>
                    <span class="text-xs">sin acceso</span>
                </li>
                <li class="flex items-center justify-between gap-2 text-slate-400">
                    <span class="text-sm">Aval (InstituLAC)</span>
                    <span class="text-xs">sin acceso</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
