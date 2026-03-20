@extends('layouts.sgd')

@section('title', 'Asesores')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Asesores</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        @if($semillero)
            Asesores internos y externos vinculados al semillero {{ $semillero->nombre }}
        @else
            Asesores del semillero
        @endif
    </p>
</div>

@if(!$semillero)
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder. No hay asesores que mostrar.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('credenciales'))
@php $credenciales = session('credenciales'); @endphp
<div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm">
    <p class="font-medium text-amber-900 mb-2">Credenciales para el asesor (guárdelas o entréguelas al asesor):</p>
    <p class="text-amber-800"><strong>Email:</strong> {{ $credenciales['email'] }}</p>
    <p class="text-amber-800"><strong>Contraseña temporal:</strong> <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono">{{ $credenciales['password'] }}</code></p>
    <p class="text-amber-700 text-xs mt-2">El asesor puede cambiar su contraseña después de ingresar al sistema.</p>
</div>
@endif
@if(session('warning'))
<div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">{{ session('warning') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="mb-4" x-data="{ modalNuevoAsesor: {{ $errors->hasAny(['nombre_completo','email','numero_documento']) ? 'true' : 'false' }}, crearCuenta: {{ old('crear_cuenta') ? 'true' : 'false' }} }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Asesores del Semillero</h2>
            <p class="text-xs text-slate-500 mt-0.5">Asesores vinculados a {{ $semillero->nombre }} — Internos y externos del semillero</p>
        </div>
        <button type="button" @click="modalNuevoAsesor = true; crearCuenta = false" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nuevo asesor
        </button>
    </div>

    {{-- Modal Crear asesor --}}
    <div x-show="modalNuevoAsesor" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevoAsesor" @click.self="modalNuevoAsesor = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevoAsesor" class="relative bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-xl">
                    <h3 class="text-lg font-semibold text-slate-900">Crear asesor</h3>
                    <button type="button" @click="modalNuevoAsesor = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('lider-sem.asesores.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600">El asesor quedará vinculado a tu semillero. Opcionalmente puedes crearle una cuenta para que ingrese al sistema con rol <strong>Asesor de Semillero</strong>.</p>
                    <div>
                        <label for="nombre_completo" class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_completo" id="nombre_completo" value="{{ old('nombre_completo') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre_completo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email_asesor" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="email_asesor" value="{{ old('email') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10" placeholder="Requerido si creas cuenta">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="telefono_asesor" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" id="telefono_asesor" value="{{ old('telefono') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="institucion_asesor" class="block text-sm font-medium text-slate-700 mb-1">Institución / Especialidad</label>
                        <input type="text" name="institucion" id="institucion_asesor" value="{{ old('institucion') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 border border-slate-100 rounded-lg">
                        <input type="checkbox" name="crear_cuenta" id="crear_cuenta" value="1" x-model="crearCuenta" class="mt-1 w-4 h-4 text-[#39A900] border-slate-300 rounded focus:ring-2 focus:ring-[#39A900]">
                        <label for="crear_cuenta" class="text-sm text-slate-700">Crear cuenta en el sistema para que pueda ingresar con rol <strong>Asesor de Semillero</strong></label>
                    </div>
                    <div x-show="crearCuenta" x-cloak>
                        <label for="numero_documento_asesor" class="block text-sm font-medium text-slate-700 mb-1">N.º de documento <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_documento" id="numero_documento_asesor" value="{{ old('numero_documento') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10" placeholder="Requerido para la cuenta">
                        @error('numero_documento') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="modalNuevoAsesor = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">Cancelar</button>
                        <button type="submit" class="sgd-btn-primary px-5 py-2.5 rounded-xl text-sm font-medium">Crear asesor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $asesoresData = [];
@endphp
<div x-data="{
    selectedAsesor: null,
    modalDetalle: false,
    modalEditar: false,
    modalEliminar: false,
    modalActivar: false,
    openDetalle(id) { this.selectedAsesor = window.asesoresData[id] || null; this.modalDetalle = true; },
    openEditar(id) { this.selectedAsesor = window.asesoresData[id] || null; this.modalEditar = true; },
    openEliminar(id) { this.selectedAsesor = window.asesoresData[id] || null; this.modalEliminar = true; },
    openActivar(id) { this.selectedAsesor = window.asesoresData[id] || null; this.modalActivar = true; },
    urlBase: '{{ url('lider-semillero/asesores') }}'
}">
<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Tipo</th>
                    <th class="text-left">Especialidad</th>
                    <th class="text-left">Cuenta</th>
                    <th class="text-left">Estado</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vinculos as $vinculo)
                @php
                    $a = $vinculo->externalAdvisor;
                    $tipo = $a && $a->user_id ? 'interno' : 'externo';
                    $tiene_cuenta = $a && (bool) $a->user_id;
                    $activo = $vinculo->activo ?? true;
                    $rowData = [
                        'vinculoId' => $vinculo->id,
                        'nombre' => $a->nombre_completo ?? '',
                        'email' => $a->email ?? '',
                        'telefono' => $a->telefono ?? '',
                        'institucion' => $a->institucion ?? '',
                        'tipo' => $tipo,
                        'tieneCuenta' => $tiene_cuenta,
                        'activo' => $activo,
                    ];
                    $asesoresData[$vinculo->id] = $rowData;
                @endphp
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $a->nombre_completo ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($tipo === 'interno')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Interno</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Externo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $a->institucion ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($tiene_cuenta)
                        <span class="text-green-700 text-sm font-medium">✓ Activo</span>
                        @else
                        <span class="text-slate-500 text-sm">Sin cuenta</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($activo)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="relative flex items-center justify-start" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute left-0 mt-2 w-52 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle({{ $vinculo->id }})"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Ver detalle</span>
                                </button>
                                <button type="button"
                                        @click="open = false; openEditar({{ $vinculo->id }})"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                    <span>Editar</span>
                                </button>
                                <button type="button"
                                        @click="open = false; openEliminar({{ $vinculo->id }})"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                                @can('asesores_externos.vincular_semillero')
                                <button type="button"
                                        @click="open = false; openActivar({{ $vinculo->id }})"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    @if($activo)
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span>Desactivar en semillero</span>
                                    @else
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Activar en semillero</span>
                                    @endif
                                </button>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                        No hay asesores vinculados a este semillero.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>window.asesoresData = @json($asesoresData ?? []);</script>

{{-- Modal Detalle --}}
    <div x-show="modalDetalle" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalle" @click.self="modalDetalle = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalDetalle" class="relative bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full" x-transition>
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Detalle del asesor</h3>
                    <button type="button" @click="modalDetalle = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="p-6 space-y-3 text-sm" x-show="selectedAsesor">
                    <p><span class="font-medium text-slate-500">Nombre:</span> <span x-text="selectedAsesor?.nombre"></span></p>
                    <p><span class="font-medium text-slate-500">Email:</span> <span x-text="selectedAsesor?.email || '—'"></span></p>
                    <p><span class="font-medium text-slate-500">Teléfono:</span> <span x-text="selectedAsesor?.telefono || '—'"></span></p>
                    <p><span class="font-medium text-slate-500">Institución / Especialidad:</span> <span x-text="selectedAsesor?.institucion || '—'"></span></p>
                    <p><span class="font-medium text-slate-500">Tipo:</span> <span x-text="selectedAsesor?.tipo === 'interno' ? 'Interno' : 'Externo'"></span></p>
                    <p><span class="font-medium text-slate-500">Cuenta en sistema:</span> <span x-text="selectedAsesor?.tieneCuenta ? 'Sí (Activo)' : 'Sin cuenta'"></span></p>
                    <p><span class="font-medium text-slate-500">Estado en semillero:</span> <span x-text="selectedAsesor?.activo ? 'Activo' : 'Inactivo'"></span></p>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                    <button type="button" @click="modalDetalle = false" class="sgd-btn-primary px-4 py-2 rounded-xl text-sm font-medium">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

{{-- Modal Editar --}}
    <div x-show="modalEditar" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditar" @click.self="modalEditar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditar" class="relative bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full" x-transition>
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Editar asesor</h3>
                    <button type="button" @click="modalEditar = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <template x-if="selectedAsesor">
                    <form :action="urlBase + '/' + selectedAsesor.vinculoId" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre_completo" :value="selectedAsesor.nombre" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" name="email" :value="selectedAsesor.email" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                                <input type="text" name="telefono" :value="selectedAsesor.telefono" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Institución / Especialidad</label>
                                <input type="text" name="institucion" :value="selectedAsesor.institucion" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end pt-2 border-t border-slate-100">
                            <button type="button" @click="modalEditar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <button type="submit" class="sgd-btn-primary px-5 py-2.5 rounded-xl text-sm font-medium">Guardar cambios</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

{{-- Modal Eliminar --}}
    <div x-show="modalEliminar" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEliminar" @click.self="modalEliminar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEliminar" class="relative bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full" x-transition>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Eliminar asesor</h3>
                    <p class="text-sm text-slate-600 mb-4" x-show="selectedAsesor">¿Desvincular a <strong x-text="selectedAsesor?.nombre"></strong> del semillero? El asesor dejará de estar vinculado a este semillero.</p>
                    <template x-if="selectedAsesor">
                        <form :action="urlBase + '/' + selectedAsesor.vinculoId" method="POST" class="flex gap-3 justify-end">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="modalEliminar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <button type="submit" @click="modalEliminar = false" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Eliminar</button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>

{{-- Modal Activar / Desactivar --}}
    <div x-show="modalActivar" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalActivar" @click.self="modalActivar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalActivar" class="relative bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full" x-transition>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2" x-text="selectedAsesor?.activo ? 'Desactivar asesor' : 'Activar asesor'"></h3>
                    <p class="text-sm text-slate-600 mb-4" x-show="selectedAsesor" x-text="selectedAsesor?.activo ? '¿Desactivar a ' + (selectedAsesor?.nombre || '') + ' en el semillero? No aparecerá como asesor activo.' : '¿Activar a ' + (selectedAsesor?.nombre || '') + ' en el semillero?'"></p>
                    <template x-if="selectedAsesor">
                        <form :action="urlBase + '/' + selectedAsesor.vinculoId + '/toggle'" method="POST" class="flex gap-3 justify-end">
                            @csrf
                            @method('PATCH')
                            <button type="button" @click="modalActivar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <button type="submit" @click="modalActivar = false" class="px-4 py-2.5 rounded-xl text-sm font-medium" :class="selectedAsesor?.activo ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-600 hover:bg-green-700 text-white'">
                                <span x-text="selectedAsesor?.activo ? 'Desactivar' : 'Activar'"></span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
