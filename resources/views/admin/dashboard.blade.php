<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Panel de Administración</h2>
        <p class="text-sm text-slate-500 mt-1">Resumen del sistema y accesos rápidos.</p>
    </div>

    {{-- 4 tarjetas de métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Usuarios Registrados</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalUsuarios }}</p>
            <p class="text-xs text-slate-500 mt-1 border-b-2 border-[#39A900] pb-0.5 w-fit">+{{ $usuariosEsteMes }} este mes</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Grupos de Investigación</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalGrupos }}</p>
            <p class="text-xs text-slate-500 mt-1">+{{ $gruposActivos }} activo(s)</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Semilleros Activos</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSemilleros }}</p>
            <p class="text-xs text-slate-500 mt-1">+{{ $semillerosEsteSemestre }} este semestre</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Centros de Formación</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCentros }}</p>
            <p class="text-xs text-slate-500 mt-1">Activos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda: dos tablas --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Usuarios recientes --}}
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Usuarios Recientes</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Últimas cuentas creadas</p>
                    </div>
                    @can('usuarios.listar')
                    <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-[#39A900] hover:text-[#2d8500] transition-colors">Ver todos</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Usuario</th>
                                <th class="text-left">Documento</th>
                                <th class="text-left">Rol</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-medium text-slate-700">{{ $user->initials() }}</span>
                                        <span class="font-medium text-slate-900">{{ $user->person?->nombre_completo ?? $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    @php
                                        $docVal = $user->tipo_documento?->value ?? '';
                                        $docPrefix = 'DOC';
                                        if (str_contains($docVal, 'cedula') && str_contains($docVal, 'ciudadana')) { $docPrefix = 'CC'; }
                                        elseif (str_contains($docVal, 'pasaporte')) { $docPrefix = 'PA'; }
                                        elseif (str_contains($docVal, 'extrangera')) { $docPrefix = 'CE'; }
                                        elseif (str_contains($docVal, 'identidad')) { $docPrefix = 'DI'; }
                                    @endphp
                                    {{ $docPrefix }} {{ $user->numero_documento }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $user->roles->first()?->name ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($user->estado && $user->estado->value === 'activo')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay usuarios recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Grupos de investigación --}}
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Grupos de Investigación</h3>
                    @if(\Illuminate\Support\Facades\Route::has('research.groups.index'))
                    <a href="{{ route('research.groups.index') }}" class="text-sm font-medium text-[#39A900] hover:text-[#2d8500]">Ver todos</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Nombre</th>
                                <th class="text-left">Código</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentResearchGroups as $group)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-900">{{ $group->nombre }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $group->codigo ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $group->trainingCenter?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($group->estado && $group->estado->value === 'activo')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay grupos de investigación.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna derecha: acciones rápidas --}}
        <div class="space-y-6">
            {{-- Acciones rápidas --}}
            <div class="sgd-card bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h3 class="text-sm font-semibold text-slate-900">Acciones Rápidas</h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    @can('usuarios.crear')
                    <a href="{{ route('admin.usuarios.create') }}" class="sgd-btn-primary flex items-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 4.5v15m7.5-7.5h-15" /></svg>
                        Nuevo Usuario
                    </a>
                    @endcan
                    @can('usuarios.asignar_rol')
                    <a href="{{ route('admin.usuarios.asignar_roles') }}" class="sgd-btn-primary flex items-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0121.75 12.75a3 3 0 01-3 3z" /></svg>
                        Asignar Rol
                    </a>
                    @endcan
                    @can('catalogos.leer')
                    <a href="{{ route('admin.training-centers.index') }}" class="sgd-btn-primary flex items-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008V21z" /></svg>
                        Centros
                    </a>
                    @endcan
                    @if(\Illuminate\Support\Facades\Route::has('admin.research-lines.index'))
                    <a href="{{ route('admin.research-lines.index') }}" class="sgd-btn-primary flex items-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                        Líneas Inv.
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
