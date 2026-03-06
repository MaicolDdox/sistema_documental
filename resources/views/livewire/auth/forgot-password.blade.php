@component('layouts.guest')
    <div class="w-full">
        <!-- Encabezado -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">Recuperar contraseña</h1>
            <p class="text-slate-500 text-sm">Ingresa tu email o email institucional para recibir un enlace de recuperación</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Email / Email institucional
                </label>
                <input type="text" name="email" id="email"
                       value="{{ old('email') }}"
                       placeholder="correo@ejemplo.com"
                       class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
                              text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all" required autofocus>
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botón submit -->
            <button type="submit"
                    class="btn-sgd w-full text-white font-semibold py-2.5 px-4
                           rounded-lg text-sm mb-4 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#39A900]">
                Enviar enlace de recuperación
            </button>
        </form>

        <div class="text-center text-sm text-slate-400">
            <span>Volver a</span>
            <a href="{{ route('login') }}" class="text-[#39A900] hover:text-[#2d8500] font-medium transition-colors">
                iniciar sesión
            </a>
        </div>
    </div>
@endcomponent
