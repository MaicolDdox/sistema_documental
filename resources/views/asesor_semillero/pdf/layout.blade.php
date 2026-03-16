<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - Asesor Semillero</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #39A900; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #0a1628; margin: 0; text-transform: uppercase; }
        .header p { color: #555; font-size: 12px; margin: 5px 0 0 0; }
        .filters { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 11px; }
        .table-wrap { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-wrap th { background-color: #0a1628; color: #fff; text-align: left; padding: 8px; font-size: 11px; text-transform: uppercase; }
        .table-wrap td { border-bottom: 1px solid #e2e8f0; padding: 8px; font-size: 11px; vertical-align: top; }
        .table-wrap tr:nth-child(even) td { background-color: #f8fafc; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-amber { background-color: #fef3c7; color: #92400e; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-purple { background-color: #f3e8ff; color: #6b21a8; }
        .metric-card { width: 30%; display: inline-block; border: 1px solid #e2e8f0; border-radius: 5px; padding: 15px; margin-right: 2%; text-align: center; background-color: #f8fafc; }
        .metric-value { font-size: 24px; font-weight: bold; color: #0a1628; margin-top: 5px; }
        .footer { text-align: center; font-size: 10px; color: #888; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@yield('title', 'Reporte')</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if(isset($request) && $request->filled('rango'))
        <div class="filters">
            <strong>Filtro aplicado:</strong>
            @if($request->rango == 'hoy') Hoy 
            @elseif($request->rango == 'semanal') Esta semana 
            @elseif($request->rango == 'mensual') Este mes 
            @elseif($request->rango == 'anual') Este año 
            @endif
        </div>
    @endif

    @yield('content')

    <div class="footer">
        Sistema de Gestión Documental SENA - Módulo Asesor Semillero
    </div>
</body>
</html>
