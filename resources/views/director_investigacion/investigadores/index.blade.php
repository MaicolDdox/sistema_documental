<x-app-layout>
    <x-slot name="header">Investigadores del Grupo</x-slot>

    <div x-data="{
            createOpen: false,
            createUrl: '{{ route('director.investigadores.create') }}?embedded=1',
            resetOpen: false,
            resetId: null,
            resetNombre: '',
            resetUrl: '',
            abrirReset(id, nombre, url) {
                this.resetId = id;
                this.resetNombre = nombre;
                this.resetUrl = url;
                this.resetOpen = true;
            }
         }">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Investigadores</h1>
                <p class="text-sm text-slate-500 mt-0.5">Miembros del grupo de investigaciÃ³n</p>
            </div>
            <button type="button"
               @click="createOpen = true"
               class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo Investigador
            </button>
        </div>

    <div class="sgd-table-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Investigador</th>
                        <th class="text-left">Correo</th>
                        <th class="text-left">Rol en grupo</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investigadores as $pivot)
                    @php $inv = $pivot->user; @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-[#39A900]">
                                        {{ strtoupper(substr($inv?->person?->primer_nombre ?? $inv?->email ?? 'U', 0, 1)) }}{{ strtoupper(substr($inv?->person?->primer_apellido ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">
                                        {{ $inv?->person?->primer_nombre ?? 'â€”' }} {{ $inv?->person?->primer_apellido ?? '' }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $inv?->numero_documento ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $inv?->email ?? 'â€”' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-md">
                                {{ ucfirst(str_replace('_', ' ', $pivot->rol->value ?? '')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if(($inv?->estado?->value ?? '') === 'activo')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="relative flex items-center justify-start" x-data="{ open: false }">
                                <button type="button"
                                        @click.stop="open = !open"
                                        @keydown.escape.window="open = false"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                        aria-haspopup="true"
                                        :aria-expanded="open ? 'true' : 'false'">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-cloak
                                     @click.away="open = false"
                                     class="absolute left-0 mt-2 w-56 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">

                                    {{-- Activar / desactivar --}}
                                    <form method="POST" action="{{ route('director.investigadores.toggle-estado', $inv) }}" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            @if(($inv?->estado?->value ?? '') === 'activo')
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m6.364-6.364A9 9 0 1112 4.5"/>
                                            </svg>
                                            <span>Desactivar investigador</span>
                                            @else
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.25v13.5m6.364-9.75A9 9 0 115.636 9"/>
                                            </svg>
                                            <span>Activar investigador</span>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Restablecer contraseña --}}
                                    <button type="button"
                                            @click="abrirReset({{ $inv->id }}, '{{ addslashes($inv?->person?->primer_nombre ?? $inv?->email ?? '') }}', '{{ route('director.investigadores.reset-password', $inv) }}'); open = false"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                        <span>Restablecer contraseña</span>
                                    </button>

                                    {{-- Editar datos --}}
                                    <a href="{{ route('director.investigadores.edit', $inv) }}"
                                       @click="open = false"
                                       class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                        <span>Editar datos</span>
                                    </a>

                                    <div class="border-t border-slate-100 my-1"></div>

                                    {{-- Desvincular --}}
                                    <form method="POST"
                                          action="{{ route('director.investigadores.desvincular', $inv) }}"
                                          class="m-0"
                                          onsubmit="return confirm('Â¿Desvincular este investigador del grupo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                            </svg>
                                            <span>Desvincular investigador</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                            No hay investigadores vinculados al grupo.
                            <br>
                            <a href="{{ route('director.investigadores.create') }}" class="text-[#39A900] hover:underline text-sm font-medium mt-2 inline-block">
                                Agregar el primero â†’
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

        {{-- â”€â”€â”€ Modal: Restablecer contraseÃ±a â”€â”€â”€ --}}
        <div x-show="resetOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full border border-slate-200 overflow-hidden"
                 @click.away="resetOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-blue-50/50">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Restablecer contraseÃ±a</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Investigador: <span class="font-medium" x-text="resetNombre"></span></p>
                    </div>
                    <button type="button" @click="resetOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="resetUrl" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    @if($errors->has('nueva_password'))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        {{ $errors->first('nueva_password') }}
                    </div>
                    @endif

                    <div x-data="{ ver1: false }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nueva contraseÃ±a <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="ver1 ? 'text' : 'password'"
                                   name="nueva_password"
                                   placeholder="MÃ­nimo 8 caracteres"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 pr-11 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <button type="button" @click="ver1 = !ver1"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                                <svg x-show="!ver1" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="ver1" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <div x-data="{ ver2: false }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirmar contraseÃ±a <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="ver2 ? 'text' : 'password'"
                                   name="nueva_password_confirmation"
                                   placeholder="Repetir contraseÃ±a"
                                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 pr-11 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                            <button type="button" @click="ver2 = !ver2"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                                <svg x-show="!ver2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="ver2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="submit"
                                class="flex-1 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold py-2.5 rounded-lg transition-all">
                            Restablecer contraseÃ±a
                        </button>
                        <button type="button" @click="resetOpen = false"
                                class="flex-1 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 rounded-lg text-sm transition-all">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal creaciÃ³n investigador (iframe con formulario completo) --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Nuevo Investigador</h2>
                    <button type="button" @click="createOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="w-full h-[80vh]">
                    <iframe
                        :src="createUrl"
                        class="w-full h-full border-0"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

@if(request()->boolean('embedded') && session('success'))
    <script>
        if (window.parent && window.parent !== window) {
            window.parent.location.reload();
        }
    </script>
@endif

{{-- Reabrir modal de reset si hay error de validaciÃ³n --}}
@if($errors->has('nueva_password'))
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.nextTick(() => {
            document.dispatchEvent(new CustomEvent('open-reset-modal'));
        });
    });
</script>
@endif

</x-app-layout>
