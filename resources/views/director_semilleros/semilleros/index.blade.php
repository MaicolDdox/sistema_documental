@extends('layouts.sgd')

@section('title', 'Semilleros de Investigación')
@section('header', '')

@section('content')
<div x-data="{
        modalNuevoSemillero: @json($errors->any() && old('_from_modal')),
        detailOpen: false,
        detailUrl: '',
        editOpen: false,
        editUrl: '',
        deleteOpen: false,
        deleteUrl: '',
        deleteName: ''
    }">
{{-- Breadcrumbs --}}
<nav class="text-sm text-slate-500 mb-2">
    <a href="{{ route('dir-sem.dashboard') }}" class="hover:text-[#39A900]">Gestión Semilleros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700 font-medium">Semilleros</span>
</nav>

<h2 class="text-xl font-bold text-slate-900 mb-0.5">Semilleros de Investigación</h2>
<p class="text-sm text-slate-500 mb-6">Tabla: semilleros — Solo semilleros asignados a tu dirección.</p>

{{-- Tabs --}}
@php $tab = $tab ?? 'todos'; @endphp
<div class="flex gap-6 border-b-2 border-slate-100 mb-4">
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'todos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'todos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Todos
    </a>
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'activos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'activos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Activos
    </a>
    <a href="{{ route('dir-sem.semilleros.index', ['tab' => 'inactivos'] + request()->only('search')) }}"
       class="sgd-tab pb-3 px-1 text-sm font-medium border-b-2 -mb-0.5 {{ $tab === 'inactivos' ? 'border-[#39A900] text-[#39A900]' : 'border-transparent text-slate-500' }}">
        Inactivos
    </a>
</div>

{{-- Buscar + Nuevo Semillero --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('dir-sem.semilleros.index') }}" class="flex gap-2 flex-1 max-w-md">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar semillero..."
                   class="sgd-input-focus w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 transition-all">
        </div>
        <button type="submit" class="sgd-btn-secondary border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-xl text-sm shrink-0">
            Buscar
        </button>
    </form>
    @can('semilleros.crear')
    <button type="button" @click="modalNuevoSemillero = true" class="sgd-btn-primary py-2.5 px-5 rounded-xl text-sm flex items-center justify-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        + Nuevo Semillero
    </button>
    @endcan
</div>

{{-- Tabla --}}
<div class="sgd-table-card bg-white">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm whitespace-nowrap">
            <thead>
                <tr>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Código</th>
                    <th class="text-left">Líder</th>
                    <th class="text-left">Grupo Inv.</th>
                    <th class="text-center">Integrantes</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semilleros as $semillero)
                <tr>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-slate-900">{{ $semillero->nombre }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                            {{ $semillero->codigo ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $leader = $semillero->leader;
                            $leaderName = $leader && $leader->person
                                ? trim(($leader->person->primer_nombre ?? '') . ' ' . ($leader->person->primer_apellido ?? ''))
                                : ($leader->email ?? '—');
                            if ($leaderName === '') {
                                $leaderName = $leader->email ?? '—';
                            }
                        @endphp
                        <p class="text-sm text-slate-700">{{ $leaderName }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($semillero->researchGroup)
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#39A900]/15 text-[#2d8500]">
                            {{ $semillero->researchGroup->nombre }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-medium text-blue-600">{{ $semillero->members->count() }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($semillero->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="relative flex items-center justify-end" x-data="{ open: false }">
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
                                 class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                @can('semilleros.ver_detalle')
                                <button type="button"
                                        @click="open = false; detailUrl = '{{ route('dir-sem.semilleros.show', $semillero) }}?embedded=1'; detailOpen = true;"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="4"/>
                                    </svg>
                                    <span>Ver detalle</span>
                                </button>
                                @endcan
                                @can('semilleros.editar')
                                <button type="button"
                                        @click="open = false; editUrl = '{{ route('dir-sem.semilleros.edit', $semillero) }}?embedded=1'; editOpen = true;"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                                    </svg>
                                    <span>Editar</span>
                                </button>
                                <button type="button"
                                        @click="open = false; deleteUrl = '{{ route('dir-sem.semilleros.destroy', $semillero) }}'; deleteName = '{{ addslashes($semillero->nombre) }}'; deleteOpen = true;"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79a48.11 48.11 0 013.478-.397m0 0V4.5c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 013.22 0C15.74 2.336 16.65 3.32 16.65 4.5v.893m0 0a48.108 48.108 0 013.478.397M4.772 5.79L4.5 19.5A2.25 2.25 0 006.75 21h10.5a2.25 2.25 0 002.25-2.25L19.228 5.79" />
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        <p class="text-slate-500">No se encontraron semilleros.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($semilleros->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/30">
        {{ $semilleros->links() }}
    </div>
    @endif
</div>

    {{-- Modal: Nuevo Semillero --}}
    @can('semilleros.crear')
    <div x-show="modalNuevoSemillero" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevoSemillero" @click.self="modalNuevoSemillero = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevoSemillero" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" x-transition>
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-slate-900">Nuevo Semillero</h3>
                    <button type="button" @click="modalNuevoSemillero = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('dir-sem.semilleros.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="_from_modal" value="1">
                    <div class="space-y-4">
                        <div>
                            <label for="modal_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre del Semillero <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="modal_nombre" value="{{ old('nombre') }}" required placeholder="Ej: Semillero de Desarrollo de Software"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('nombre') border-red-300 @enderror">
                            @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="modal_codigo" class="block text-sm font-medium text-slate-700 mb-1">Código</label>
                                <input type="number" name="codigo" id="modal_codigo" value="{{ old('codigo', $siguienteCodigo ?? '') }}" min="1" step="1" placeholder="{{ $siguienteCodigo ?? 'Auto' }}"
                                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('codigo') border-red-300 @enderror">
                                @error('codigo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="modal_research_group_id" class="block text-sm font-medium text-slate-700 mb-1">Grupo de Investigación</label>
                                <select name="research_group_id" id="modal_research_group_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('research_group_id') border-red-300 @enderror">
                                    <option value="">Ninguno</option>
                                    @foreach($gruposInvestigacion ?? [] as $grupo)
                                        <option value="{{ $grupo->id }}" {{ old('research_group_id') == $grupo->id ? 'selected' : '' }}>{{ $grupo->nombre }} @if($grupo->codigo)({{ $grupo->codigo }})@endif</option>
                                    @endforeach
                                </select>
                                @error('research_group_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="modal_lider_id" class="block text-sm font-medium text-slate-700 mb-1">Líder Asignado <span class="text-red-500">*</span></label>
                            <select name="lider_id" id="modal_lider_id" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('lider_id') border-red-300 @enderror">
                                <option value="">Selecciona un líder...</option>
                                @foreach($lideres ?? [] as $lider)
                                    @php
                                        $nombreLider = $lider->person ? trim(($lider->person->primer_nombre ?? '') . ' ' . ($lider->person->primer_apellido ?? '')) : $lider->email;
                                        if ($nombreLider === '') { $nombreLider = $lider->email; }
                                    @endphp
                                    <option value="{{ $lider->id }}" {{ old('lider_id') == $lider->id ? 'selected' : '' }}>{{ $nombreLider }} ({{ $lider->email }})</option>
                                @endforeach
                            </select>
                            @error('lider_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="modal_descripcion" class="block text-sm font-medium text-slate-700 mb-1">Descripción (opcional)</label>
                            <textarea name="descripcion" id="modal_descripcion" rows="3" placeholder="Propósito, líneas de investigación..." class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 @error('descripcion') border-red-300 @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end pt-5 mt-5 border-t border-slate-100">
                        <button type="button" @click="modalNuevoSemillero = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Guardar Semillero
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal detalle semillero (iframe) --}}
    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-semibold text-slate-900">Detalle del semillero</h2>
                <button type="button" @click="detailOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="w-full h-[80vh]">
                <iframe
                    :src="detailUrl"
                    class="w-full h-full border-0"
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>

    {{-- Modal eliminar semillero (estilo administrador) --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 bg-red-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l7.5-15 7.5 15h-15z" />
                        </svg>
                    </span>
                    <h2 class="text-sm font-semibold text-red-800">Eliminar semillero</h2>
                </div>
                <button type="button" @click="deleteOpen = false" class="p-1.5 rounded-lg hover:bg-red-100 text-red-500" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-2">
                <p class="text-sm text-slate-700">¿Estás seguro de que deseas eliminar el semillero:</p>
                <p class="text-sm font-semibold text-slate-900" x-text="deleteName"></p>
                <p class="text-xs text-slate-500 mt-1">Esta acción no se puede deshacer y puede afectar registros relacionados.</p>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button"
                        @click="deleteOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-white">
                    Cancelar
                </button>
                <form :action="deleteUrl" method="POST" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal editar semillero (iframe) --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-semibold text-slate-900">Editar semillero</h2>
                <button type="button" @click="editOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="w-full h-[80vh]">
                <iframe
                    :src="editUrl"
                    class="w-full h-full border-0"
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>
@endsection
