<div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Perfil</h2>
            <p class="text-sm text-slate-500 mt-0.5">Actualiza tu información personal y de contacto</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form wire:submit="updateProfileInformation" class="space-y-5">
                {{-- Nombres --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer nombre</label>
                        <input type="text" wire:model="primer_nombre"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required autofocus>
                        @error('primer_nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo nombre</label>
                        <input type="text" wire:model="segundo_nombre"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                {{-- Apellidos --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primer apellido</label>
                        <input type="text" wire:model="primer_apellido"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required>
                        @error('primer_apellido') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Segundo apellido</label>
                        <input type="text" wire:model="segundo_apellido"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                {{-- Emails --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo personal</label>
                        <input type="email" wire:model="email"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                               required>
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo institucional</label>
                        <input type="email" wire:model="email_institucional"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('email_institucional') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Contacto --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" wire:model="telefono"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Celular</label>
                        <input type="text" wire:model="celular"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                </div>

                <div class="border-t border-slate-100 my-4"></div>

                {{-- Datos Complementarios --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Género</label>
                        <select wire:model="genero" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Selecciona...</option>
                            @foreach($generos as $g)
                                <option value="{{ $g->value }}">{{ $g->value }}</option>
                            @endforeach
                        </select>
                        @error('genero') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">EPS</label>
                        <input type="text" wire:model="eps"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('eps') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Institucional SENA --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cargo / Posición</label>
                        <select wire:model="entity_position_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Selecciona...</option>
                            @foreach($entityPositions as $ep)
                                <option value="{{ $ep->id }}">{{ $ep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('entity_position_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Vinculación</label>
                            <select wire:model="linkage_type_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="">Selecciona...</option>
                                @foreach($linkageTypes as $lt)
                                    <option value="{{ $lt->id }}">{{ $lt->nombre }}</option>
                                @endforeach
                            </select>
                            @error('linkage_type_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Programa de Formación</label>
                            <select wire:model="training_program_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="">Selecciona...</option>
                                @foreach($trainingPrograms as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                                @endforeach
                            </select>
                            @error('training_program_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 mt-2 border-t border-slate-100">
                    <button type="submit"
                            class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all focus:ring-2 focus:ring-[#39A900]/50 outline-none">
                        Guardar cambios
                    </button>
                    <x-action-message class="text-sm font-medium text-green-600" on="profile-updated">
                        Perfil actualizado.
                    </x-action-message>
                </div>
            </form>
        </div>
    </div>
