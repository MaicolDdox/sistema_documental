<div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Integrante</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($semillero->members as $integrante)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 flex-shrink-0">
                            {{ $integrante->initials() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">
                                {{ $integrante->person->primer_nombre ?? '' }} {{ $integrante->person->primer_apellido ?? '' }}
                            </p>
                            @php
                                $fichaCodigo = $integrante->person?->enrollments?->first()?->trainingRecord?->training_record_code ?? null;
                            @endphp
                            <p class="text-xs text-slate-400 mt-0.5">
                                Ficha: {{ $fichaCodigo ?: 'N/A' }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ $integrante->numero_documento }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ $integrante->email }}
                </td>
                <td class="px-4 py-3 text-center">
                    @if($integrante->estado === \App\Enums\EstadoEnum::Activo)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                            Inactivo
                        </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                    <p>No hay integrantes registrados en este semillero.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
