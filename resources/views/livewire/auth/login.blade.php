<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Iniciar sesión')" :description="__('Ingresa tu tipo y número de documento con tu contraseña')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Tipo de documento -->
            <div>
                <flux:select name="tipo_documento" :label="__('Tipo de documento')" required autofocus>
                    <option value="">Selecciona un tipo...</option>
                    <option value="cedula ciudadana" {{ old('tipo_documento') === 'cedula ciudadana' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                    <option value="documento identidad" {{ old('tipo_documento') === 'documento identidad' ? 'selected' : '' }}>Documento de Identidad</option>
                    <option value="pasaporte" {{ old('tipo_documento') === 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                    <option value="cedula extrangera" {{ old('tipo_documento') === 'cedula extrangera' ? 'selected' : '' }}>Cédula Extranjera</option>
                </flux:select>
                @error('tipo_documento')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Número de documento -->
            <flux:input
                name="numero_documento"
                :label="__('Número de documento')"
                :value="old('numero_documento')"
                type="number"
                required
                placeholder="Ej: 34327134"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Contraseña')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Contraseña')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Ingresar') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
