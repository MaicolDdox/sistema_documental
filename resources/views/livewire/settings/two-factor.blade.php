<section class="max-w-2xl" wire:cloak>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">{{ __('Two Factor Authentication') }}</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ __('Manage your two-factor authentication settings') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        @if ($twoFactorEnabled)
            <div class="space-y-4 text-sm text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        {{ __('Enabled') }}
                    </span>
                </div>

                <p>
                    {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                </p>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>
                </div>

                <div class="flex justify-start pt-4">
                    <button wire:click="disable"
                            class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-4 rounded-lg text-sm transition-all border border-red-200">
                        {{ __('Disable 2FA') }}
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-4 text-sm text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                        {{ __('Disabled') }}
                    </span>
                </div>

                <p>
                    {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                </p>

                <div class="pt-4">
                    <button wire:click="enable"
                            class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-5 rounded-lg text-sm transition-all">
                        {{ __('Enable 2FA') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal for Setup -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity"
             style="backdrop-filter: blur(2px);">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden max-w-md w-full"
                 @click.away="$wire.closeModal()">
                
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $this->modalConfig['title'] }}</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600 mb-6 text-center">
                        {{ $this->modalConfig['description'] }}
                    </p>

                    @if ($showVerificationStep)
                        <div class="space-y-6">
                            <div class="flex flex-col items-center space-y-3 justify-center">
                                <label class="block text-sm font-medium text-slate-700 text-center mb-1.5">Código OTP de tu App</label>
                                <input type="text" wire:model.live="code"
                                       class="w-48 text-center text-tracking-widest letter-spacing-[0.2em] font-mono text-xl border border-slate-300 rounded-lg px-4 py-3 text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all"
                                       maxlength="6" placeholder="123456" autofocus>
                                @error('code')
                                    <p class="text-sm text-red-600 text-center">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center space-x-3 pt-4">
                                <button wire:click="resetVerification"
                                        class="flex-1 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                                    {{ __('Back') }}
                                </button>
                                <button wire:click="confirmTwoFactor"
                                        {{ strlen($code ?? '') < 6 ? 'disabled' : '' }}
                                        class="flex-1 bg-[#39A900] hover:bg-[#2d8500] disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                                    {{ __('Confirm') }}
                                </button>
                            </div>
                        </div>
                    @else
                        @error('setupData')
                            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex justify-center mb-6">
                            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm inline-block">
                                @empty($qrCodeSvg)
                                    <div class="w-48 h-48 bg-slate-100 animate-pulse rounded-lg"></div>
                                @else
                                    {!! $qrCodeSvg !!}
                                @endempty
                            </div>
                        </div>

                        <button wire:click="showVerificationIfNecessary"
                                class="w-full bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all mb-6">
                            {{ $this->modalConfig['buttonText'] }}
                        </button>

                        <div class="space-y-4">
                            <div class="relative flex items-center justify-center w-full">
                                <div class="absolute inset-0 w-full h-px top-1/2 bg-slate-200"></div>
                                <span class="relative px-3 text-xs uppercase font-medium bg-white text-slate-400 tracking-wider">
                                    {{ __('or, enter the code manually') }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2"
                                 x-data="{
                                     copied: false,
                                     copy() {
                                         navigator.clipboard.writeText('{{ $manualSetupKey }}').then(() => {
                                             this.copied = true;
                                             setTimeout(() => this.copied = false, 1500);
                                         });
                                     }
                                 }">
                                <div class="flex items-stretch w-full border border-slate-300 rounded-lg overflow-hidden bg-slate-50">
                                    @empty($manualSetupKey)
                                        <div class="p-3 w-full text-center text-slate-400 text-sm">{{ __('Loading...') }}</div>
                                    @else
                                        <input type="text" readonly value="{{ $manualSetupKey }}"
                                               class="w-full p-3 bg-transparent outline-none text-slate-700 font-mono text-sm tracking-wider" />
                                        <button @click="copy()" type="button"
                                                class="px-4 transition-colors border-l border-slate-300 bg-white hover:bg-slate-50 text-slate-500 flex items-center justify-center cursor-pointer">
                                            <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <svg x-show="copied" style="display: none;" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
