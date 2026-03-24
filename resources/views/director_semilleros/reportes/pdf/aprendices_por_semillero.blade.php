<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Aprendices por Semillero' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 16px; color: #39A900; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <p>Generado: {{ now('America/Bogota')->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Semillero</th>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas ?? [] as $f)
            <tr>
                <td>{{ $f['semillero'] }}</td>
                <td>{{ $f['documento'] }}</td>
                <td>{{ $f['nombre'] }}</td>
                <td>{{ $f['email'] }}</td>
            </tr>
            @empty
            <tr><td colspan="4">Sin datos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">SGD - Sistema de Gestión Documental</div>
</body>
</html>
