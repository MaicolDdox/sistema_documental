<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Semilleros con Métricas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; color: #39A900; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>{{ $titulo ?? 'Semilleros con Métricas' }}</h1>
    <p>Generado: {{ now('America/Bogota')->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Semillero</th>
                <th>Código</th>
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
                <td>{{ $f['integrantes'] }}</td>
                <td>{{ $f['proyectos'] }}</td>
                <td>{{ $f['productos'] }}</td>
            </tr>
            @empty
            <tr><td colspan="5">Sin datos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">SGD - Sistema de Gestión Documental</div>
</body>
</html>
