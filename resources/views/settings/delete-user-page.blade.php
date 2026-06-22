<x-settings-layout>
    <section class="max-w-2xl mt-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Eliminar cuenta</h2>
            <p class="text-sm text-slate-500 mt-0.5">Elimina tu cuenta y todos sus recursos permanentemente</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="mb-6 text-sm text-slate-600">
                <p>Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán borrados permanentemente. Antes de eliminar tu cuenta, descarga cualquier información que desees conservar.</p>
            </div>

            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm transition-all shadow-sm">
                Eliminar cuenta
            </button>
        </div>

        {{-- Modal de confirmación --}}
        <div x-data="{ show: false }"
             x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true"
             x-on:close-modal.window="show = false"
             x-on:keydown.escape.window="show = false"
             x-show="show"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity"
             style="backdrop-filter: blur(2px); display: none;">

            <div class="bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden max-w-lg w-full"
                 @click.away="show = false">

                <form action="{{ route('settings.delete-user.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">¿Estás seguro de que quieres eliminar tu cuenta?</h3>
                        <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-slate-600">
                            Una vez eliminada, toda la información asociada a tu cuenta será borrada permanentemente. Ingresa tu contraseña para confirmar.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                            <input type="password" name="password"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 transition-all"
                                   placeholder="Tu contraseña actual" />
                            @error('password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="show = false"
                                class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-4 rounded-lg text-sm transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all shadow-sm">
                            Eliminar cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-settings-layout>
