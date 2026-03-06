<div class="w-full">
    <!-- Encabezado -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 mb-1">Iniciar sesión</h1>
        <p class="text-slate-500 text-sm">Ingresa con tu documento institucional</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm text-green-600 text-center">{{ session('status') }}</div>
    @endif

    <form wire:submit="login">

        <!-- Tipo de documento -->
        <div class="mb-4">
            <label for="tipo_documento" class="block text-sm font-medium text-slate-700 mb-1.5">
                Tipo de documento
            </label>
            <select wire:model="tipo_documento" id="tipo_documento"
                    class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
                           text-sm text-slate-800 bg-white transition-all" required autofocus>
                <option value="">Selecciona...</option>
                <option value="cedula ciudadana">Cédula de Ciudadanía</option>
                <option value="documento identidad">Documento de Identidad</option>
                <option value="pasaporte">Pasaporte</option>
                <option value="cedula extranjera">Cédula Extranjera</option>
            </select>
            @error('tipo_documento')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Número de documento -->
        <div class="mb-4">
            <label for="numero_documento" class="block text-sm font-medium text-slate-700 mb-1.5">
                Número de documento
            </label>
            <input type="number" wire:model="numero_documento" id="numero_documento"
                   placeholder="Ej: 34327134"
                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
                          text-sm text-slate-800 transition-all" required>
            @error('numero_documento')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contraseña -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-slate-700">
                    Contraseña
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-[#39A900] hover:text-[#2d8500] font-medium transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <input type="password" wire:model="password" id="password"
                   placeholder="••••••••"
                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
                          text-sm text-slate-800 transition-all" required>
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Recordarme -->
        <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" wire:model="remember" id="remember"
                   class="rounded border-slate-300 text-[#39A900] focus:ring-[#39A900]">
            <label for="remember" class="text-sm text-slate-600">Recordarme</label>
        </div>

        <!-- Botón submit -->
        <button type="submit"
                class="btn-sgd w-full text-white font-semibold py-2.5 px-4
                       rounded-lg text-sm">
            Ingresar al sistema
        </button>
    </form>
</div>
