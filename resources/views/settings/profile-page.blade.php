<x-settings-layout>
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Perfil</h2>
            <p class="text-sm text-slate-500 mt-0.5">Actualiza tu información personal y de contacto</p>
        </div>

        @if(session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-lg"
        >
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PATCH')

                {{-- Nombres --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer nombre</label>
                        <input type="text" name="primer_nombre"
                               value="{{ old('primer_nombre', $person?->primer_nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required autofocus>
                        @error('primer_nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo nombre</label>
                        <input type="text" name="segundo_nombre"
                               value="{{ old('segundo_nombre', $person?->segundo_nombre) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                {{-- Apellidos --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer apellido</label>
                        <input type="text" name="primer_apellido"
                               value="{{ old('primer_apellido', $person?->primer_apellido) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required>
                        @error('primer_apellido') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo apellido</label>
                        <input type="text" name="segundo_apellido"
                               value="{{ old('segundo_apellido', $person?->segundo_apellido) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                {{-- Emails --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo personal</label>
                        <input type="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required>
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo institucional</label>
                        <input type="email" name="email_institucional"
                               value="{{ old('email_institucional', $person?->email_institucional) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('email_institucional') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Contacto --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="telefono"
                               value="{{ old('telefono', $person?->telefono) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Celular</label>
                        <input type="text" name="celular"
                               value="{{ old('celular', $person?->celular) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                <div class="border-t border-slate-100 my-4"></div>

                {{-- Datos Complementarios --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Género</label>
                        <select name="genero" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Selecciona...</option>
                            @foreach($generos as $g)
                                <option value="{{ $g->value }}" {{ old('genero', $person?->genero?->value) === $g->value ? 'selected' : '' }}>
                                    {{ $g->value }}
                                </option>
                            @endforeach
                        </select>
                        @error('genero') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">EPS</label>
                        <input type="text" name="eps"
                               value="{{ old('eps', $person?->eps) }}"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('eps') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Institucional SENA --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cargo / Posición</label>
                        <select name="entity_position_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Selecciona...</option>
                            @foreach($entityPositions as $ep)
                                <option value="{{ $ep->id }}" {{ old('entity_position_id', $person?->entity_position_id) == $ep->id ? 'selected' : '' }}>
                                    {{ $ep->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('entity_position_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Vinculación</label>
                            <select name="linkage_type_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="">Selecciona...</option>
                                @foreach($linkageTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ old('linkage_type_id', $person?->linkage_type_id) == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('linkage_type_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">CVLAC</label>
                            <input type="text" name="cvlac_link" value="{{ old('cvlac_link', $person?->cvlac_link) }}"
                                   placeholder="https://scienti.minciencias.gov.co/cvlac/..."
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            @error('cvlac_link') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('co_investigador'))
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nivel de Formación</label>
                            <select name="nivel_formacion" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="">Selecciona...</option>
                                @foreach(\App\Enums\NivelFormacionEnum::cases() as $nf)
                                    <option value="{{ $nf->value }}" {{ old('nivel_formacion', $person?->nivel_formacion?->value) == $nf->value ? 'selected' : '' }}>
                                        {{ $nf->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nivel_formacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de Vinculación</label>
                            <input type="date" name="fecha_vinculacion"
                                   value="{{ old('fecha_vinculacion', $person?->fecha_vinculacion?->format('Y-m-d')) }}"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            @error('fecha_vinculacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endif
                </div>

                @if($errors->has('general'))
                <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $errors->first('general') }}
                </div>
                @endif

                <div class="flex items-center gap-4 pt-4 mt-2 border-t border-slate-100">
                    <button type="submit"
                            class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all focus:ring-2 focus:ring-[#39A900]/50 outline-none">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-settings-layout>
