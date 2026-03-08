<div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyecto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Categoría</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($semillero->projects as $proyecto)
                <!-- Simulando que los proyectos tienen productos, usamos los product o groupProducts de la db.
                     Si no hay productos, esto quedará vacío.
                 -->
                @php
                    // Dependiendo de la estructura, aquí se mostrarían los productos vinculados a los proyectos.
                    // $productos = $proyecto->products ?? []; // Ajustar a relación real
                    $productos = []; 
                @endphp
                
                @foreach($productos as $producto)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-slate-900">{{ $producto->nombre }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ Str::limit($proyecto->title, 40) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $producto->categoria ?? 'Sin categorizar' }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-slate-500">
                        {{ $producto->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                    <p>No se encontraron productos desarrollados por este semillero.</p>
                </td>
            </tr>
            @endforelse
            
            @if($semillero->projects->count() > 0 && empty($productos))
            {{-- Fallback message si sí hay proyectos pero no hay productos iterados --}}
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                    <p>No hay productos registrados en los proyectos del semillero aún.</p>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
