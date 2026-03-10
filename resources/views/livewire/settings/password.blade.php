<div class="max-w-2xl"
     x-data="{ saved: false, error: false }"
     x-on:password-updated.window="saved = true; error = false; setTimeout(() => saved = false, 4000)"
     x-on:password-error.window="error = true; saved = false; setTimeout(() => error = false, 5000)">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Actualizar contraseña</h2>
        <p class="text-sm text-slate-500 mt-0.5">Asegúrate de usar una contraseña segura y difícil de adivinar</p>
    </div>

    {{-- Alerta de éxito --}}
    <div x-show="saved"
         style="display:none"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-green-800">¡Contraseña actualizada correctamente!</p>
        <button @click="saved = false" class="ml-auto text-green-400 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Alerta de error general --}}
    <div x-show="error"
         style="display:none"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <p class="text-sm font-medium text-red-800">No se pudo actualizar la contraseña. Verifica los campos.</p>
        <button @click="error = false" class="ml-auto text-red-400 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
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

            <div class="pt-2">
                <button type="submit"
                        class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Actualizar contraseña
                </button>
            </div>
        </form>
    </div>
</div>

