<x-layouts::auth>
    <div class="w-full">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">Verifica tu correo</h1>
            <p class="text-slate-500 text-sm">
                Por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te enviamos.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200">
                <p class="text-sm text-green-700 font-medium">
                    Se envió un nuevo enlace de verificación a tu correo institucional.
                </p>
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="btn-sgd w-full text-white font-semibold py-2.5 px-4 rounded-lg text-sm">
                    Reenviar enlace de verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-sm text-slate-500 hover:text-slate-700 font-medium py-2 transition-colors"
                        data-test="logout-button">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</x-layouts::auth>
