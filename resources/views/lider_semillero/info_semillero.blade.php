@extends('layouts.sgd')

@section('title', 'Info del Semillero')
@section('header', '')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Info del Semillero</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                @if($semillero)
                    Editar información del semillero {{ $semillero->nombre }}
                @else
                    Información de tu semillero
                @endif
            </p>
        </div>
        @if($semillero)
        <button type="submit" form="form-info-semillero" class="sgd-btn-primary px-5 py-2.5 rounded-xl text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Guardar cambios
        </button>
        @endif
    </div>

    @if(!$semillero)
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
            <p class="text-sm text-slate-600 mb-6 max-w-md mx-auto">
                No tienes un semillero asignado como líder. El Director de Semilleros debe asignarte como líder de un semillero para que puedas editar su información aquí.
            </p>
            <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Dashboard
            </a>
        </div>
    </div>
    @else
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
            <h2 class="text-base font-semibold text-slate-900 font-heading">Información del Semillero</h2>
            <p class="text-xs text-slate-500 mt-0.5">Puedes editar nombre, logo y descripción únicamente.</p>
        </div>

        <form action="{{ route('lider-sem.info-semillero.update') }}" method="POST" enctype="multipart/form-data" id="form-info-semillero">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <h3 class="text-sm font-semibold text-slate-800">Editar — Semillero {{ $semillero->nombre }}</h3>

                {{-- Logo --}}
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <label class="block text-sm font-medium text-slate-700">Logo</label>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <div class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0" id="logo-preview-box">
                            @if(!empty($semillero->logo))
                                <img id="logo-preview" src="{{ asset('storage/' . $semillero->logo) }}" alt="Logo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling && this.nextElementSibling.classList.remove('hidden');">
                                <svg id="logo-placeholder" class="w-10 h-10 text-[#39A900] hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            @else
                                <svg id="logo-placeholder" class="w-10 h-10 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <input type="file" name="logo" id="input-logo" accept="image/jpeg,image/png,image/gif,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#39A900]/10 file:text-[#39A900] hover:file:bg-[#39A900]/20 file:cursor-pointer cursor-pointer">
                            <p class="text-xs text-slate-500">Formatos: JPG, PNG, GIF o WebP. Máximo 2 MB.</p>
                            <p id="logo-file-name" class="text-xs text-green-600 font-medium hidden"></p>
                        </div>
                    </div>
                </div>

                {{-- Nombre del semillero * --}}
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del semillero <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $semillero->nombre) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all sgd-input-focus">
                    @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Código (solo lectura) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Código (solo lectura)</label>
                    <input type="text" value="{{ $semillero->codigo ?? '—' }}" readonly
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-500 bg-slate-100 cursor-not-allowed">
                </div>

                {{-- Descripción --}}
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="4"
                              class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all sgd-input-focus">{{ old('descripcion', $semillero->descripccion) }}</textarea>
                    @error('descripcion') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Grupo de investigación (solo lectura) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo de investigación (solo lectura)</label>
                    <input type="text" value="{{ $semillero->researchGroup?->nombre ?? '—' }}" readonly
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-500 bg-slate-100 cursor-not-allowed">
                </div>

                {{-- Estado (solo lectura) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado (solo lectura)</label>
                    <div class="flex items-center gap-2 border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-100">
                        @if(($semillero->estado->value ?? '') === 'activo')
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-sm text-slate-600">Activo</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-sm text-slate-600">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="px-6 pb-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-secondary px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700">
                    Cancelar
                </a>
                <button type="submit" class="sgd-btn-primary px-5 py-2.5 rounded-xl text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.getElementById('form-info-semillero')?.addEventListener('submit', function() {
        document.querySelectorAll('button[form="form-info-semillero"], #form-info-semillero button[type="submit"]').forEach(function(btn) {
            btn.disabled = true;
            btn.innerHTML = 'Guardando…';
        });
    });

    // Al elegir un archivo, mostrar nombre y vista previa del logo
    document.getElementById('input-logo')?.addEventListener('change', function(e) {
        var file = e.target.files && e.target.files[0];
        var nameEl = document.getElementById('logo-file-name');
        var box = document.getElementById('logo-preview-box');
        if (!file) {
            if (nameEl) { nameEl.classList.add('hidden'); nameEl.textContent = ''; }
            return;
        }
        if (nameEl) {
            nameEl.textContent = 'Nuevo archivo: ' + file.name;
            nameEl.classList.remove('hidden');
        }
        if (box && file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function() {
                var img = document.getElementById('logo-preview');
                var placeholder = document.getElementById('logo-placeholder');
                if (img) {
                    img.src = reader.result;
                } else {
                    var newImg = document.createElement('img');
                    newImg.id = 'logo-preview';
                    newImg.alt = 'Logo';
                    newImg.className = 'w-full h-full object-cover';
                    newImg.src = reader.result;
                    if (placeholder) placeholder.remove();
                    box.appendChild(newImg);
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection
