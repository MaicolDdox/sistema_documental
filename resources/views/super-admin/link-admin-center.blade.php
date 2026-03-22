<x-app-layout>
    <x-slot name="header">Vincular centro con administrador</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Migas">
        <a href="{{ route('super-admin.dashboard') }}" class="hover:text-slate-700">Super administrador</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Centro ↔ administrador</span>
    </nav>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-amber-200/80 bg-gradient-to-r from-amber-50 to-white px-4 py-4 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Vincular centro de formación con administrador</h2>
        <p class="text-sm text-slate-600 mt-1 max-w-3xl">
            <strong>Centros:</strong> solo los que <strong>aún no tienen</strong> un administrador del sistema vinculado.
            <strong>Administradores:</strong> solo quienes tienen rol de administrador del sistema (o <code class="text-xs bg-amber-100/80 px-1 rounded">admin</code>) y <strong>aún no tienen centro</strong> (nunca el super administrador).
            Créalos en <strong>Usuarios</strong> con ese rol y deja el centro vacío, o quita el centro en edición, para que aparezcan aquí.
            Lo que ya aparece en el resumen a la derecha no sale en los desplegables.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Nueva vinculación</h3>
            <form method="POST" action="{{ route('super-admin.centros-administradores.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="training_center_id" class="block text-sm font-medium text-slate-700 mb-1">Centro de formación</label>
                    <select name="training_center_id" id="training_center_id" required
                            class="w-full rounded-lg border-slate-300 text-sm @error('training_center_id') border-red-500 @enderror">
                        <option value="">Seleccionar centro…</option>
                        @forelse($centrosDisponibles as $c)
                            <option value="{{ $c->id }}" @selected((int) old('training_center_id') === $c->id)>
                                {{ $c->nombre }} @if(filled($c->codigo)) ({{ $c->codigo }}) @endif
                                @if(! $c->activo) — inactivo @endif
                            </option>
                        @empty
                            <option value="" disabled>No hay centros libres: todos ya tienen administrador vinculado.</option>
                        @endforelse
                    </select>
                    @error('training_center_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-slate-700 mb-1">Administrador del sistema</label>
                    <select name="user_id" id="user_id" required
                            class="w-full rounded-lg border-slate-300 text-sm @error('user_id') border-red-500 @enderror">
                        <option value="">Seleccionar usuario…</option>
                        @forelse($administradores as $u)
                            @php
                                $label = $u->person?->nombre_completo ?? $u->email;
                                $doc = $u->numero_documento;
                            @endphp
                            <option value="{{ $u->id }}" @selected((int) old('user_id') === $u->id)>
                                {{ $label }} — {{ $u->email }} (CC {{ $doc }})
                            </option>
                        @empty
                            <option value="" disabled>No hay administradores sin centro: crea uno en Usuarios o todos ya están vinculados.</option>
                        @endforelse
                    </select>
                    @error('user_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" @disabled($administradores->isEmpty() || $centrosDisponibles->isEmpty())
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] disabled:opacity-50 disabled:pointer-events-none transition-colors">
                    Guardar vinculación
                </button>
            </form>
        </div>

        <div class="sgd-table-card bg-white overflow-hidden border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                <h3 class="text-sm font-semibold text-slate-900">Resumen por centro</h3>
                <p class="text-xs text-slate-500 mt-0.5">Administradores del sistema asignados actualmente</p>
            </div>
            <div class="overflow-x-auto max-h-[28rem] overflow-y-auto">
                <table class="sgd-table text-sm w-full">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr>
                            <th class="text-left">Centro</th>
                            <th class="text-left">Administrador(es)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centers as $c)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-900">{{ $c->nombre }}</td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    @forelse($c->users as $u)
                                        <div class="text-xs">{{ $u->person?->nombre_completo ?? $u->email }} <span class="text-slate-400">({{ $u->email }})</span></div>
                                    @empty
                                        <span class="text-slate-400 italic">Sin administrador vinculado</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
