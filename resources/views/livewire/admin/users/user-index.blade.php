<div>
    <flux:heading size="xl">{{ __('Gestión de Usuarios') }}</flux:heading>

    @if (session('status'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-600 text-sm font-medium">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="mt-6 flex flex-col sm:flex-row gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email o documento..." class="flex-1" />
        <select wire:model.live="filterEstado" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
        <select wire:model.live="filterRole" class="rounded-lg border-slate-300 text-sm">
            <option value="">Todos los roles</option>
            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
            + Nuevo Usuario
        </a>
    </div>

    {{-- Tabla --}}
    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 dark:border-neutral-700">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-neutral-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Nombre</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Documento</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Rol</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-600">Estado</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-neutral-700">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/50">
                        <td class="px-4 py-3">
                            {{ $user->person?->primer_nombre }} {{ $user->person?->primer_apellido }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $user->email ?? $user->person?->email_institucional ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $user->tipo_documento?->value }} {{ $user->numero_documento }}
                        </td>
                        <td class="px-4 py-3">
                            @foreach($user->roles as $role)
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleEstado({{ $user->id }})"
                                    wire:confirm="¿Cambiar estado del usuario?"
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full cursor-pointer transition-colors
                                    {{ $user->estado->value === 'activo' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ ucfirst($user->estado->value) }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
