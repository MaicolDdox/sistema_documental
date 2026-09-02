<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proyecto</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        .header { border: 1px solid #d1d5db; background: #f8fafc; padding: 10px 12px; border-radius: 8px; }
        .title { font-size: 16px; color: #166534; margin: 0 0 4px; font-weight: bold; }
        .meta { font-size: 9px; color: #475569; margin: 1px 0; }
        .section { margin-top: 14px; }
        .section-title { font-size: 12px; font-weight: bold; color: #166534; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #dcfce7; color: #166534; font-weight: bold; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 8px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $proyecto->nombre }}</p>
        <p class="meta"><strong>Semillero:</strong> {{ $proyecto->seedling?->nombre ?? '—' }}</p>
        <p class="meta"><strong>Líder de Proyecto:</strong> {{ $proyecto->liderProyecto?->person?->nombre_completo ?? $proyecto->liderProyecto?->email ?? '—' }}</p>
        <p class="meta"><strong>Estado:</strong> {{ $proyecto->estado?->value ?? '—' }} | <strong>Línea de Investigación:</strong> {{ $proyecto->researchLine?->nombre ?? '—' }}</p>
        <p class="meta"><strong>Generado por:</strong> {{ $meta['usuario'] ?? '—' }} | <strong>Fecha:</strong> {{ $meta['generado_en'] ?? '—' }}</p>
    </div>

    <div class="section">
        <p class="section-title">Evidencias de Producto Final ({{ $productoFinal->count() }})</p>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado 1ra etapa (Líder Semillero)</th>
                    <th>Estado 2da etapa (Director)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productoFinal as $e)
                <tr>
                    <td>{{ $e->nombre }}</td>
                    <td>{{ $e->estado_revision_lider?->value ?? '—' }}</td>
                    <td>{{ $e->estado_revision_director?->value ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="3">Sin evidencias de producto final</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <p class="section-title">Evidencias de Desarrollo ({{ $desarrollo->count() }})</p>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($desarrollo as $e)
                <tr>
                    <td>{{ $e->nombre }}</td>
                    <td>{{ $e->descripcion ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="2">Sin evidencias de desarrollo</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <p class="section-title">Aprendices ({{ $aprendices->count() }})</p>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Ficha</th>
                    <th>Tecnólogo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aprendices as $a)
                <tr>
                    <td>{{ $a->nombre_completo }}</td>
                    <td>{{ $a->numero_documento }}</td>
                    <td>{{ $a->ficha ?? '—' }}</td>
                    <td>{{ $a->trainingProgram?->nombre ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4">Sin aprendices registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <p class="section-title">Co-investigadores vinculados ({{ $coinvestigadores->count() }})</p>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coinvestigadores as $c)
                <tr>
                    <td>{{ $c->person?->nombre_completo ?? '—' }}</td>
                    <td>{{ $c->email }}</td>
                </tr>
                @empty
                <tr><td colspan="2">Sin co-investigadores vinculados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">SGD - Sistema de Gestión Documental</div>
</body>
</html>
