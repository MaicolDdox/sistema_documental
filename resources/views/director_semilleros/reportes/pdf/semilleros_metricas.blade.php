<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Semilleros con Métricas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        .header { border: 1px solid #d1d5db; background: #f8fafc; padding: 10px 12px; border-radius: 8px; }
        .title { font-size: 16px; color: #166534; margin: 0 0 4px; font-weight: bold; }
        .meta { font-size: 9px; color: #475569; margin: 1px 0; }
        .summary { margin: 10px 0 12px; width: 100%; border-collapse: collapse; }
        .summary td { border: 1px solid #bfdbfe; background: #eff6ff; padding: 6px 8px; font-size: 9px; }
        .chart { border: 1px solid #d1d5db; background: #f8fafc; padding: 8px; margin-bottom: 10px; }
        .chart-title { font-size: 11px; font-weight: bold; color: #166534; margin: 0 0 2px; }
        .chart-desc { font-size: 8px; color: #64748b; margin: 0 0 6px; }
        .bar-row { margin: 3px 0; font-size: 8px; }
        .bar-track { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; }
        .bar-fill { height: 8px; background: #39A900; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #dcfce7; color: #166534; font-weight: bold; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 8px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $titulo ?? 'Semilleros con Métricas' }}</p>
        <p class="meta"><strong>Centro:</strong> {{ $meta['centro'] ?? '—' }}</p>
        <p class="meta"><strong>Generado por:</strong> {{ $meta['usuario'] ?? '—' }} | <strong>Fecha:</strong> {{ $meta['generado_en'] ?? now('America/Bogota')->format('d/m/Y H:i') }}</p>
        <p class="meta"><strong>Filtro fechas:</strong> {{ $meta['fecha_desde'] ?? 'Sin filtro' }} - {{ $meta['fecha_hasta'] ?? 'Sin filtro' }}</p>
    </div>
    @if(!empty($resumen))
    <table class="summary">
        <tr>
            <td><strong>Semilleros:</strong> {{ $resumen['total_semilleros'] ?? 0 }}</td>
            <td><strong>Integrantes:</strong> {{ $resumen['total_integrantes'] ?? 0 }}</td>
            <td><strong>Proyectos activos:</strong> {{ $resumen['total_proyectos'] ?? 0 }}</td>
            <td><strong>Productos:</strong> {{ $resumen['total_productos'] ?? 0 }}</td>
        </tr>
    </table>
    @endif
    @if(!empty($chart['items']))
        @php $maxChart = collect($chart['items'])->max('value') ?: 1; @endphp
        <div class="chart">
            <p class="chart-title">{{ $chart['title'] ?? 'Gráfica' }}</p>
            <p class="chart-desc">{{ $chart['description'] ?? '' }}</p>
            @foreach($chart['items'] as $item)
                @php $pct = round((($item['value'] ?? 0) / $maxChart) * 100); @endphp
                <div class="bar-row">
                    <strong>{{ $item['label'] ?? 'N/A' }}</strong> ({{ $item['value'] ?? 0 }})
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>Semillero</th>
                <th>Código</th>
                <th>Líder</th>
                <th>Asesores</th>
                <th>Integrantes</th>
                <th>Proyectos activos</th>
                <th>Productos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas ?? [] as $f)
            <tr>
                <td>{{ $f['nombre'] }}</td>
                <td>{{ $f['codigo'] }}</td>
                <td>{{ $f['lider'] ?? '—' }}</td>
                <td>{{ $f['asesores'] ?? 0 }}</td>
                <td>{{ $f['integrantes'] }}</td>
                <td>{{ $f['proyectos'] }}</td>
                <td>{{ $f['productos'] }}</td>
            </tr>
            @empty
            <tr><td colspan="7">Sin datos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">SGD - Sistema de Gestión Documental</div>
</body>
</html>
