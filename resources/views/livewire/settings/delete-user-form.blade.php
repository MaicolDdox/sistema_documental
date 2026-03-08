<section class="max-w-2xl mt-8">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">{{ __('Delete account') }}</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="mb-6 text-sm text-slate-600">
            <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
        </div>
        
        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm transition-all shadow-sm">
            {{ __('Delete account') }}
        </button>
    </div>

    <div x-data="{ show: false }"
         x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true"
         x-on:close-modal.window="show = false"
         x-on:keydown.escape.window="show = false"
         x-show="show"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity"
         style="backdrop-filter: blur(2px); display: none;" wire:ignore.self>
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden max-w-lg w-full"
             @click.away="show = false">
             
            <form method="POST" wire:submit="deleteUser">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Are you sure you want to delete your account?') }}</h3>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-slate-600">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ __('Password') }}</label>
                        <input type="password" wire:model="password"
                               class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 transition-all"
                               placeholder="{{ __('Password') }}" />
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="show = false"
                            class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-4 rounded-lg text-sm transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all shadow-sm">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
