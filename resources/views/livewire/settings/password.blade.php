<div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Actualizar contraseña</h2>
            <p class="text-sm text-slate-500 mt-0.5">Asegúrate de usar una contraseña segura y difícil de adivinar</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form wire:submit="updatePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña actual</label>
                    <input type="password" wire:model="current_password"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                           required autocomplete="current-password">
                    @error('current_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nueva contraseña</label>
                    <input type="password" wire:model="password"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                           required autocomplete="new-password">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                    <input type="password" wire:model="password_confirmation"
                           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 transition-all focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"
                           required autocomplete="new-password">
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit"
                            class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all">
                        Actualizar contraseña
                    </button>
                    <x-action-message class="text-sm text-green-600" on="password-updated">
                        Guardado correctamente.
                    </x-action-message>
                </div>
            </form>
        </div>
    </div>
