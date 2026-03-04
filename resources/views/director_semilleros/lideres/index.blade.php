@extends('director_semilleros.layout')

@section('title', 'Líderes de Semillero')
@section('header', 'Directorio de Líderes')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-sm font-semibold text-slate-800">Líderes del Centro</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de usuarios con rol de Líder de Semillero de tu centro de formación.</p>
    </div>
    @can('usuarios.crear_lider_semillero')
    <a href="{{ route('dir-sem.lideres.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nuevo Líder
    </a>
    @endcan
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contacto</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Registro</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lideres as $lider)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                {{ $lider->initials() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $lider->person->primer_nombre ?? '' }} {{ $lider->person->primer_apellido ?? '' }}</p>
                                <p class="text-xs text-slate-500">Líder de Semillero</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $lider->tipo_documento ?? 'CC' }} - {{ $lider->numero_documento }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-slate-900">{{ $lider->email }}</p>
                        @if($lider->person && $lider->person->telefono)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $lider->person->telefono }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($lider->estado === \App\Enums\EstadoEnum::Activo)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-slate-500 text-xs">
                        {{ $lider->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <p>No se encontraron líderes de semillero.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($lideres->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
        {{ $lideres->links() }}
    </div>
    @endif
</div>
@endsection
