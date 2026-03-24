<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte') - Sistema Documental</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; line-height: 1.4; color: #334155; margin: 0; padding: 0; background-color: #fff; }
        
        /* HEADER */
        .header { background-color: #0a1628; color: #fff; padding: 20px; border-bottom: 4px solid #39A900; margin-bottom: 25px; border-radius: 4px; position: relative; }
        .header-content { display: table; width: 100%; }
        .header-left { display: table-cell; width: 70%; vertical-align: middle; }
        .header-right { display: table-cell; width: 30%; text-align: right; vertical-align: middle; }
        .header h1 { margin: 0 0 5px 0; font-size: 24px; font-weight: bold; color: #fff; }
        .header p { margin: 0; font-size: 13px; color: #94a3b8; }
        
        /* BADGE RANGO */
        .badge-rango { background-color: #39A900; color: #fff; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; display: inline-block; margin-top: 10px; }
        
        /* METRICS ROW */
        .metrics-row { display: table; width: 100%; margin-bottom: 25px; table-layout: fixed; border-spacing: 12px 0; }
        .metric-card { display: table-cell; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; text-align: left; }
        .metric-card.metric-green { border-left: 4px solid #39A900; }
        .metric-card.metric-blue { border-left: 4px solid #2563eb; }
        .metric-card.metric-amber { border-left: 4px solid #d97706; }
        .metric-card.metric-red { border-left: 4px solid #dc2626; }
        .metric-card.metric-purple { border-left: 4px solid #9333ea; }
        
        .metric-label { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .metric-value { font-size: 24px; font-weight: bold; color: #0f172a; display: block; margin-bottom: 3px; }
        .metric-sub { font-size: 10px; color: #94a3b8; }
        
        /* SECCIONES & TABLAS */
        .section-title { font-size: 16px; font-weight: bold; color: #0f172a; margin: 0 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; }
        .table-wrap { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        thead th { background-color: #f1f5f9; color: #475569; font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 8px 10px; text-align: left; border-top: 1px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; }
        tbody td { padding: 9px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; vertical-align: middle; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        /* BADGES INLINE */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        
        /* GRÁFICOS CSS */
        .chart-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 20px; }
        .chart-title { font-size: 12px; font-weight: bold; color: #0a1628; margin-bottom: 15px; text-transform: uppercase; }
        .chart-bar-container { margin-bottom: 15px; }
        .chart-label { font-size: 10px; color: #64748b; margin-bottom: 5px; display: block; font-weight: bold; }
        .bar-track { width: 100%; background-color: #e2e8f0; border-radius: 10px; height: 12px; overflow: hidden; position: relative; }
        .bar-fill { height: 100%; border-radius: 10px; position: absolute; left: 0; top: 0; }
        .chart-legend { display: table; width: 100%; margin-top: 5px; }
        .chart-legend-item { display: table-cell; font-size: 10px; color: #475569; width: 50%; }
        .chart-legend-right { text-align: right; font-weight: bold; color: #0f172a; }

        /* UTILES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-muted { color: #64748b; }
        font-semibold { font-weight: bold; color: #1e293b; }
        .empty-state { padding: 20px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px; color: #94a3b8; font-style: italic; }
        
        /* FOOTER */
        .footer { padding-top: 15px; margin-top: 30px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <h1>Reporte: @yield('title')</h1>
                <p>Generado por: {{ auth()->user()->person ? auth()->user()->person->primer_nombre . ' ' . auth()->user()->person->primer_apellido : auth()->user()->email }} - Rol: Asesor Semillero</p>
                <div class="badge-rango">Período: {{ $etiqueta_rango ?? 'Histórico Completo' }}</div>
            </div>
            <div class="header-right">
                <p style="color: #cbd5e1; font-weight: bold; font-size: 18px;">SENA</p>
                <p>Fecha de emisión: <br>{{ now('America/Bogota')->format('d/m/Y h:i A') }}</p>
            </div>
        </div>
    </div>

    @yield('content')

    <div class="footer">
        Este documento es generado automáticamente por el Sistema de Gestión Documental del SENA. Página <span class="pagenum"></span>
    </div>
</body>
</html>
