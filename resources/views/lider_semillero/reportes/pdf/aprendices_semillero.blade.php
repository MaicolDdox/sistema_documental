<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Aprendices por Semillero' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        .header { border: 1px solid #d1d5db; background: #f8fafc; padding: 10px 12px; border-radius: 8px; }
        .title { font-size: 16px; color: #166534; margin: 0 0 4px; font-weight: bold; }
        .meta { font-size: 9px; color: #475569; margin: 1px 0; }
        .summary { margin: 10px 0 12px; width: 100%; border-collapse: collapse; }
        .summary td { border: 1px solid #bfdbfe; background: #eff6ff; padding: 6px 8px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #dcfce7; color: #166534; font-weight: bold; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 8px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $titulo ?? 'Aprendices por Semillero' }}</p>
        <p class="meta"><strong>Semillero:</strong> {{ $meta['semillero'] ?? '—' }}</p>
        <p class="meta"><strong>Generado por:</strong> {{ $meta['usuario'] ?? '—' }} | <strong>Fecha:</strong> {{ $meta['generado_en'] ?? now('America/Bogota')->format('d/m/Y H:i') }}</p>
    </div>
    @if(!empty($resumen))
    <table class="summary">
        <tr>
            <td><strong>Total aprendices:</strong> {{ $resumen['total_aprendices'] ?? 0 }}</td>
        </tr>
    </table>
    @endif
    <table>
        <thead>
            <tr>
                <th>Proyecto</th>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Ficha</th>
                <th>Tecnólogo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas ?? [] as $f)
            <tr>
                <td>{{ $f['proyecto'] }}</td>
                <td>{{ $f['documento'] }}</td>
                <td>{{ $f['nombre'] }}</td>
                <td>{{ $f['ficha'] ?? '—' }}</td>
                <td>{{ $f['tecnologo'] ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5">Sin datos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">SGD - Sistema de Gestión Documental</div>
</body>
</html>
