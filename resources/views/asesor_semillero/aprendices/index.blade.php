<x-app-layout>
@php
    $aprendicesParaEditar = $aprendices ? $aprendices->keyBy('id') : collect();
    if (isset($editAprendiz) && $editAprendiz && !$aprendicesParaEditar->has($editAprendiz->id)) {
        $aprendicesParaEditar->put($editAprendiz->id, $editAprendiz);
    }
@endphp
<div x-data="{
        openCreate: {{ request('registrar') || ($errors->any() && old('_from_modal')) ? 'true' : 'false' }},
        showDetailId: null,
        editId: {{ $editAprendiz?->id ?? 'null' }},
        confirmDeleteId: null
    }">
{{-- Mensaje local de éxito (además del global en layout) --}}
@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

{{-- Botón Registrar en la parte superior --}}
@can('aprendices.registrar')
<div class="mb-4 flex justify-end">
    <button type="button"
            @click="openCreate = true"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
            style="background:#39A900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Registrar Aprendiz
    </button>
</div>
@endcan
{{-- Buscador --}}
<form method="GET" class="mb-4 flex gap-3">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o documento..."
               class="w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
    </div>
    <button type="submit" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Filtrar</button>
    @if(request('buscar'))
        <a href="{{ route('asesor.aprendices.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">Limpiar</a>
    @endif
</form>

@if(!$semillero)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <p class="text-amber-800 text-sm">⚠️ No tienes un semillero asignado.</p>
    </div>
@elseif($aprendices->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        <p class="text-slate-500 text-sm">No hay aprendices registrados en tu semillero.</p>
        @can('aprendices.registrar')
            <button type="button"
                    @click="openCreate = true"
                    class="mt-3 inline-block text-sm font-medium"
                    style="color:#39A900">
                Registrar el primer aprendiz →
            </button>
        @endcan
    </div>
@else
<div class="sgd-table-card bg-white overflow-x-auto rounded-xl border border-slate-200/80 shadow-sm">
    <table class="sgd-table aprendices-table min-w-[720px] table-fixed">
        <thead>
            <tr>
                <th class="w-[28%] text-left py-3.5 px-4">Aprendiz</th>
                <th class="w-[18%] text-left py-3.5 px-4">Documento</th>
                <th class="w-[22%] text-left py-3.5 px-4">Correo institucional</th>
                <th class="w-[12%] text-left py-3.5 px-4">Celular</th>
                <th class="w-[20%] text-right py-3.5 px-4 pr-5">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
            @foreach($aprendices as $ap)
            @php $p = $ap->person; @endphp
            <tr class="hover:bg-emerald-50/50 transition-colors">
                {{-- Aprendiz: nombre + programa --}}
                <td class="py-4 px-4 align-middle text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, #0a1628 0%, #1e3a5f 100%);">
                            {{ strtoupper(substr($p?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($p?->primer_apellido ?? 'P', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ $p?->primer_nombre }} {{ $p?->segundo_nombre }} {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $p?->trainingProgram?->nombre ?? 'Sin programa' }}
                            </p>
                        </div>
                    </div>
                </td>
                {{-- Documento: tipo + número --}}
                <td class="py-4 px-4 align-middle text-left">
                    <div class="space-y-0.5">
                        <p class="text-xs text-slate-500 capitalize">
                            {{ str_replace(['_', 'cedula '], [' ', 'Cédula '], $ap->tipo_documento?->value ?? '—') }}
                        </p>
                        <p class="text-sm font-mono font-medium text-slate-700">{{ $ap->numero_documento }}</p>
                    </div>
                </td>
                {{-- Correo --}}
                <td class="py-4 px-4 align-middle text-left">
                    <a href="mailto:{{ $p?->email_institucional }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline truncate block max-w-[200px]" title="{{ $p?->email_institucional ?? '—' }}">
                        {{ $p?->email_institucional ?? '—' }}
                    </a>
                </td>
                {{-- Celular --}}
                <td class="py-4 px-4 align-middle text-left">
                    @if($p?->celular)
                        <a href="tel:{{ $p?->celular }}" class="text-sm text-slate-700 hover:text-emerald-600 font-medium">{{ $p?->celular }}</a>
                    @else
                        <span class="text-slate-400 text-sm">—</span>
                    @endif
                </td>
                {{-- Acciones --}}
                <td class="py-4 px-4 pr-5 align-middle text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button type="button"
                                @click="showDetailId = {{ $ap->id }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-all"
                                title="Ver detalles">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s2.25-6.75 9.75-6.75S21.75 12 21.75 12 19.5 18.75 12 18.75 2.25 12 2.25 12z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
                        <button type="button"
                                @click="editId = {{ $ap->id }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-emerald-200 text-emerald-600 bg-emerald-50/80 hover:bg-emerald-100 transition-all"
                                title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a1.5 1.5 0 012.121 2.121L8.621 15.97a4.5 4.5 0 01-1.591 1.03L5 17.75l.75-2.03a4.5 4.5 0 011.03-1.591l10.082-10.642z" />
                            </svg>
                        </button>
                        <form method="POST" id="delete-form-{{ $ap->id }}" action="{{ route('asesor.aprendices.destroy', $ap->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    @click="confirmDeleteId = {{ $ap->id }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 text-red-500 bg-red-50/50 hover:bg-red-100 transition-all"
                                    title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Paginación --}}
@if($aprendices->hasPages())
<div class="mt-4">{{ $aprendices->links() }}</div>
@endif
@endif

{{-- Modal detalle del aprendiz --}}
<div x-show="showDetailId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true" x-transition>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-slate-200"
         @click.self="showDetailId = null">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-slate-100 rounded-t-2xl z-10">
            <h2 class="text-base font-semibold text-slate-900">Detalle del aprendiz</h2>
            <button type="button" @click="showDetailId = null" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            @foreach($aprendices as $ap)
            @php $p = $ap->person; @endphp
            <div x-show="showDetailId === {{ $ap->id }}" x-cloak class="space-y-5">
                {{-- Avatar + nombre --}}
                <div class="flex items-center gap-4 pb-5 border-b border-slate-100">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, #0a1628 0%, #1e3a5f 100%);">
                        {{ strtoupper(substr($p?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($p?->primer_apellido ?? 'P', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 text-lg">
                            {{ $p?->primer_nombre }} {{ $p?->segundo_nombre }} {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $p?->entityPosition?->nombre ?? '—' }}</p>
                    </div>
                </div>

                {{-- Documento --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Documento</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-400">Tipo</p>
                            <p class="text-sm font-medium text-slate-700 capitalize">{{ str_replace(['_', 'cedula '], [' ', 'Cédula '], $ap->tipo_documento?->value ?? '—') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Número</p>
                            <p class="text-sm font-medium text-slate-700">{{ $ap->numero_documento }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Género</p>
                            <p class="text-sm font-medium text-slate-700 capitalize">{{ $p?->genero ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">EPS</p>
                            <p class="text-sm font-medium text-slate-700">{{ $p?->eps ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Contacto --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Contacto</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-400">Celular</p>
                            <p class="text-sm font-medium text-slate-700">{{ $p?->celular ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Teléfono fijo</p>
                            <p class="text-sm font-medium text-slate-700">{{ $p?->telefono ?? '—' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-400">Correo institucional</p>
                            <p class="text-sm font-medium text-slate-700 break-all">{{ $p?->email_institucional ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Formación --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Formación</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-slate-400">Programa de formación</p>
                            <p class="text-sm font-medium text-slate-700">
                                {{ $p?->trainingProgram?->nombre ?? '—' }}
                                @if($p?->trainingProgram?->trainingProgramType)
                                    <span class="text-slate-500">({{ $p->trainingProgram->trainingProgramType->nombre }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Tipo de vinculación</p>
                            <p class="text-sm font-medium text-slate-700">{{ $p?->linkageType?->nombre ?? '—' }}</p>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modal editar aprendiz --}}
@if($semillero && $aprendicesParaEditar->isNotEmpty())
<div x-show="editId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true" x-transition>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-slate-200" @click.self="editId = null">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-slate-100 rounded-t-2xl z-10">
            <h2 class="text-base font-semibold text-slate-900">Editar aprendiz</h2>
            <button type="button" @click="editId = null" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800">
                El correo institucional se usa como identificador de inicio de sesión. Solo modifícalo si es un error tipográfico.
            </div>
            @foreach($aprendicesParaEditar as $ap)
            @php $p = $ap->person; @endphp
            <form method="POST" action="{{ route('asesor.aprendices.update', $ap->id) }}" novalidate x-show="editId === {{ $ap->id }}" x-cloak class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos personales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Primer nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="primer_nombre" value="{{ old('primer_nombre', $p?->primer_nombre) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('primer_nombre') border-red-400 @enderror">
                            @error('primer_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Segundo nombre</label>
                            <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre', $p?->segundo_nombre) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Primer apellido <span class="text-red-500">*</span></label>
                            <input type="text" name="primer_apellido" value="{{ old('primer_apellido', $p?->primer_apellido) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('primer_apellido') border-red-400 @enderror">
                            @error('primer_apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Segundo apellido</label>
                            <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $p?->segundo_apellido) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                            <select name="tipo_documento" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('tipo_documento') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach(['cedula ciudadana','documento identidad','pasaporte','cedula extrangera'] as $tipo)
                                    <option value="{{ $tipo }}" {{ old('tipo_documento', $ap->tipo_documento?->value) == $tipo ? 'selected' : '' }}>{{ ucfirst(str_replace('cedula','cédula',$tipo)) }}</option>
                                @endforeach
                            </select>
                            @error('tipo_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                            <input type="number" name="numero_documento" value="{{ old('numero_documento', $ap->numero_documento) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('numero_documento') border-red-400 @enderror">
                            @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Género <span class="text-red-500">*</span></label>
                            <select name="genero" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('genero') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="masculino" {{ old('genero', $p?->genero) == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="femenino" {{ old('genero', $p?->genero) == 'femenino' ? 'selected' : '' }}>Femenino</option>
                                <option value="prefiero no decirlo" {{ old('genero', $p?->genero) == 'prefiero no decirlo' ? 'selected' : '' }}>Prefiero no decirlo</option>
                            </select>
                            @error('genero') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">EPS <span class="text-red-500">*</span></label>
                            <input type="text" name="eps" value="{{ old('eps', $p?->eps) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('eps') border-red-400 @enderror">
                            @error('eps') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Celular <span class="text-red-500">*</span></label>
                            <input type="number" name="celular" value="{{ old('celular', $p?->celular) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('celular') border-red-400 @enderror">
                            @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono fijo</label>
                            <input type="number" name="telefono" value="{{ old('telefono', $p?->telefono) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">Datos académicos e institucionales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Correo institucional <span class="text-red-500">*</span></label>
                            <input type="email" name="email_institucional" value="{{ old('email_institucional', $p?->email_institucional) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('email_institucional') border-red-400 @enderror">
                            @error('email_institucional') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cargo / Rol <span class="text-red-500">*</span></label>
                            <select name="entity_position_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('entity_position_id') border-red-400 @enderror">
                                <option value="">Seleccionar cargo...</option>
                                @foreach($cargos as $grupo => $lista)
                                    <optgroup label="{{ $grupo }}">
                                        @foreach($lista as $cargo)
                                            <option value="{{ $cargo->id }}" {{ old('entity_position_id', $p?->entity_position_id) == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('entity_position_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de vinculación <span class="text-red-500">*</span></label>
                            <select name="linkage_type_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('linkage_type_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($tiposVinculacion as $tv)
                                    <option value="{{ $tv->id }}" {{ old('linkage_type_id', $p?->linkage_type_id) == $tv->id ? 'selected' : '' }}>{{ $tv->nombre }}</option>
                                @endforeach
                            </select>
                            @error('linkage_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Programa de formación <span class="text-red-500">*</span></label>
                            <select name="training_program_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('training_program_id') border-red-400 @enderror">
                                <option value="">Seleccionar programa...</option>
                                @foreach($programasFormacion as $pf)
                                    <option value="{{ $pf->id }}" {{ old('training_program_id', $p?->training_program_id) == $pf->id ? 'selected' : '' }}>{{ $pf->nombre }} @if($pf->trainingProgramType)({{ $pf->trainingProgramType->nombre }})@endif</option>
                                @endforeach
                            </select>
                            @error('training_program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editId = null" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90" style="background:#39A900">Guardar cambios</button>
                </div>
            </form>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Modal confirmar eliminación --}}
<div x-show="confirmDeleteId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true">
    <div class="relative bg-slate-900 text-white rounded-2xl shadow-2xl max-w-sm w-full px-6 py-5">
        <div class="flex items-start gap-3">
            <div class="mt-0.5">
                <div class="w-8 h-8 rounded-full bg-red-500/10 border border-red-500/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5M12 15.75h.007v.008H12v-.008z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75l1.5 9A1.5 1.5 0 007.49 21h9.02a1.5 1.5 0 001.49-1.25l1.5-9M10.5 5.25h3M9 5.25A1.5 1.5 0 0110.5 3.75h3A1.5 1.5 0 0115 5.25M4.5 9.75h15" />
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold mb-1.5">
                    ¿Seguro que deseas eliminar este aprendiz del semillero?
                </p>
                <p class="text-xs text-slate-300">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button"
                    @click="confirmDeleteId = null"
                    class="px-4 py-2.5 rounded-lg text-xs font-medium border border-slate-600 text-slate-200 hover:bg-slate-800 transition-all">
                Cancelar
            </button>
            <button type="button"
                    @click="
                        const f = document.getElementById('delete-form-' + confirmDeleteId);
                        if (f) { f.submit(); }
                        confirmDeleteId = null;
                    "
                    class="px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-red-500 hover:bg-red-600 shadow-sm transition-all">
                Aceptar
            </button>
        </div>
    </div>
</div>


{{-- Modal Registrar Aprendiz (misma línea visual que otros modales) --}}
@can('aprendices.registrar')
<div x-show="openCreate" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="openCreate" @click.self="openCreate = false" class="fixed inset-0 bg-black/40" x-transition></div>
        <div x-show="openCreate" class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-slate-200" x-transition>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Registrar aprendiz</h2>
                <p class="text-xs text-slate-500 mt-1">
                    El aprendiz quedará registrado con <span class="font-semibold">estado inactivo</span>.
                </p>
            </div>
            <button type="button"
                    @click="openCreate = false"
                    class="p-1.5 rounded-full hover:bg-slate-100 text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            </div>

            <div class="px-6 py-5">
            <form method="POST" action="{{ route('asesor.aprendices.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_from_modal" value="1">

                {{-- Datos personales --}}
                <div class="mb-5">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">
                        Datos personales
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Primer nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="primer_nombre" value="{{ old('primer_nombre') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_nombre') border-red-400 @enderror">
                            @error('primer_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Segundo nombre</label>
                            <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Primer apellido <span class="text-red-500">*</span></label>
                            <input type="text" name="primer_apellido" value="{{ old('primer_apellido') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('primer_apellido') border-red-400 @enderror">
                            @error('primer_apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Segundo apellido</label>
                            <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                            <select name="tipo_documento" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('tipo_documento') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="cedula ciudadana" {{ old('tipo_documento') == 'cedula ciudadana' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="documento identidad" {{ old('tipo_documento') == 'documento identidad' ? 'selected' : '' }}>Documento de Identidad</option>
                                <option value="pasaporte" {{ old('tipo_documento') == 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                                <option value="cedula extrangera" {{ old('tipo_documento') == 'cedula extrangera' ? 'selected' : '' }}>Cédula Extranjera</option>
                            </select>
                            @error('tipo_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                            <input type="number" name="numero_documento" value="{{ old('numero_documento') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('numero_documento') border-red-400 @enderror">
                            @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Género <span class="text-red-500">*</span></label>
                            <select name="genero" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('genero') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="masculino" {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                                <option value="prefiero no decirlo" {{ old('genero') == 'prefiero no decirlo' ? 'selected' : '' }}>Prefiero no decirlo</option>
                            </select>
                            @error('genero') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">EPS <span class="text-red-500">*</span></label>
                            <input type="text" name="eps" value="{{ old('eps') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('eps') border-red-400 @enderror">
                            @error('eps') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Celular <span class="text-red-500">*</span></label>
                            <input type="number" name="celular" value="{{ old('celular') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('celular') border-red-400 @enderror">
                            @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono fijo</label>
                            <input type="number" name="telefono" value="{{ old('telefono') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        </div>
                    </div>
                </div>

                {{-- Datos académicos --}}
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3 pb-2 border-b border-slate-100">
                        Datos académicos e institucionales
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Correo institucional <span class="text-red-500">*</span></label>
                            <input type="email" name="email_institucional" value="{{ old('email_institucional') }}" placeholder="aprendiz@sena.edu.co"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('email_institucional') border-red-400 @enderror">
                            @error('email_institucional') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cargo / Rol <span class="text-red-500">*</span></label>
                            <select name="entity_position_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('entity_position_id') border-red-400 @enderror">
                                <option value="">Seleccionar cargo...</option>
                                @foreach($cargos as $grupo => $lista)
                                    <optgroup label="{{ $grupo }}">
                                        @foreach($lista as $cargo)
                                            <option value="{{ $cargo->id }}" {{ old('entity_position_id') == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('entity_position_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de vinculación <span class="text-red-500">*</span></label>
                            <select name="linkage_type_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('linkage_type_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($tiposVinculacion as $tv)
                                    <option value="{{ $tv->id }}" {{ old('linkage_type_id') == $tv->id ? 'selected' : '' }}>{{ $tv->nombre }}</option>
                                @endforeach
                            </select>
                            @error('linkage_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Programa de formación <span class="text-red-500">*</span></label>
                            <select name="training_program_id" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('training_program_id') border-red-400 @enderror">
                                <option value="">Seleccionar programa...</option>
                                @foreach($programasFormacion as $pf)
                                    <option value="{{ $pf->id }}" {{ old('training_program_id') == $pf->id ? 'selected' : '' }}>
                                        {{ $pf->nombre }} @if($pf->trainingProgramType)({{ $pf->trainingProgramType->nombre }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('training_program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
                    <button type="button"
                            @click="openCreate = false"
                            class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
                            style="background:#39A900">
                        Registrar Aprendiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
</div>

</x-app-layout>
