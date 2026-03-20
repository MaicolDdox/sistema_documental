<x-app-layout>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">
        {{ $grupo->exists ? 'Editar Grupo de Investigación' : 'Nuevo Grupo de Investigación' }}
    </h1>
</div>

@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm space-y-1">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif

<form method="POST"
      action="{{ $grupo->exists ? route('admin.research-groups.update', $grupo) : route('admin.research-groups.store') }}"
      class="space-y-6 max-w-3xl">
    @csrf
    @if($grupo->exists) @method('PUT') @endif
    @if(request()->boolean('embedded'))
        <input type="hidden" name="embedded" value="1">
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 sgd-card">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Centro de Formación <span class="text-red-500">*</span></label>
            <select name="training_center_id"
                    class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                    required>
                <option value="">Seleccione...</option>
                @foreach($centros as $c)
                    <option value="{{ $c->id }}" {{ old('training_center_id', $grupo->training_center_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del grupo <span class="text-red-500">*</span></label>
            <input type="text" name="nombre" value="{{ old('nombre', $grupo->nombre) }}"
                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                <input type="text" name="codigo" value="{{ old('codigo', $grupo->codigo) }}"
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                <select name="estado"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                        required>
                    <option value="{{ \App\Enums\EstadoEnum::Activo->value }}" {{ old('estado', $grupo->estado?->value ?? 'activo') === \App\Enums\EstadoEnum::Activo->value ? 'selected' : '' }}>Activo</option>
                    <option value="{{ \App\Enums\EstadoEnum::Inactivo->value }}" {{ old('estado', $grupo->estado?->value) === \App\Enums\EstadoEnum::Inactivo->value ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
            <textarea name="descripccion" rows="3"
                      class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">{{ old('descripccion', $grupo->descripccion) }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 sgd-card">
        <h3 class="text-sm font-semibold text-slate-900">Responsable del grupo</h3>
        <p class="text-xs text-slate-500">Selecciona el usuario que actuará como director/líder del grupo de investigación.</p>
        @php
            $respActualId = isset($responsable) ? $responsable->id : null;
        @endphp
        <select name="responsable_id"
                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            <option value="">Sin responsable</option>
            @foreach($usuarios as $u)
                <option value="{{ $u->id }}" {{ old('responsable_id', $respActualId) == $u->id ? 'selected' : '' }}>
                    {{ $u->name }} ({{ $u->email }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            {{ $grupo->exists ? 'Guardar cambios' : 'Crear grupo' }}
        </button>
        <a href="{{ route('admin.research-groups.index') }}"
           class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            Cancelar
        </a>
    </div>
</form>

</x-app-layout>

@if(request()->boolean('embedded') && session('success'))
    <script>
        if (window.parent && window.parent !== window) {
            window.parent.location.reload();
        }
    </script>
@endif


