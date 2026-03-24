<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Investigación — SENA</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1e293b; background:#fff; }

/* ─── Cabecera institucional ─── */
.header-wrap { background:#fff; border-bottom: 3px solid #39A900; padding:14px 24px 12px; display:table; width:100%; }
.header-logo-cell { display:table-cell; vertical-align:middle; width:70px; }
.header-logo-cell img { width:56px; height:auto; }
.header-org-cell { display:table-cell; vertical-align:middle; padding-left:12px; border-left:2px solid #dcfce7; margin-left:8px; }
.header-org-cell .org-name { font-size:12px; font-weight:bold; color:#166534; line-height:1.2; }
.header-org-cell .org-sub  { font-size:8px; color:#64748b; margin-top:2px; }
.header-org-cell .org-dep  { font-size:8px; color:#39A900; font-weight:bold; margin-top:1px; }
.header-doc-cell { display:table-cell; vertical-align:middle; text-align:right; width:200px; }
.header-doc-cell .doc-label { font-size:15px; font-weight:bold; color:#0f172a; line-height:1.2; }
.header-doc-cell .doc-sub   { font-size:8px; color:#64748b; margin-top:2px; }

/* ─── Banda de color ─── */
.band { height:5px; background:linear-gradient(90deg, #166534 0%, #39A900 50%, #86efac 100%); }

/* ─── Info del reporte ─── */
.report-info { background:#f0fdf4; border:1px solid #dcfce7; border-radius:6px; padding:10px 16px; margin:14px 24px; display:table; width:calc(100% - 48px); }
.ri-cell { display:table-cell; vertical-align:middle; padding-right:20px; }
.ri-label { font-size:8px; font-weight:bold; text-transform:uppercase; color:#64748b; letter-spacing:.5px; margin-bottom:2px; }
.ri-value { font-size:10px; font-weight:bold; color:#166534; }

/* ─── Cuerpo ─── */
.body { padding:0 24px 24px; }

/* ─── Título de sección ─── */
.sec-title {
    font-size:10px; font-weight:bold; color:#fff;
    background:#166534;
    padding:6px 12px;
    border-radius:4px 4px 0 0;
    text-transform:uppercase; letter-spacing:.7px;
    margin-top:16px;
}

/* ─── Tarjetas métricas ─── */
.metrics-table { width:100%; border-collapse:separate; border-spacing:6px; margin:8px 0; }
.metric-cell { padding:10px 14px; border-radius:6px; text-align:center; }
.metric-cell .m-num   { font-size:28px; font-weight:bold; line-height:1; }
.metric-cell .m-label { font-size:8px; text-transform:uppercase; letter-spacing:.5px; margin-top:3px; }
.m-default { background:#f8fafc; border:1px solid #e2e8f0; color:#0f172a; }
.m-default .m-label { color:#64748b; }
.m-green   { background:#f0fdf4; border:1px solid #bbf7d0; }
.m-green   .m-num   { color:#15803d; }
.m-green   .m-label { color:#166534; }
.m-amber   { background:#fffbeb; border:1px solid #fde68a; }
.m-amber   .m-num   { color:#b45309; }
.m-amber   .m-label { color:#92400e; }
.m-red     { background:#fef2f2; border:1px solid #fecaca; }
.m-red     .m-num   { color:#dc2626; }
.m-red     .m-label { color:#991b1b; }
.m-blue    { background:#eff6ff; border:1px solid #bfdbfe; }
.m-blue    .m-num   { color:#1d4ed8; }
.m-blue    .m-label { color:#1e40af; }

/* ─── Tabla de datos ─── */
.data-table { width:100%; border-collapse:collapse; font-size:9px; }
.data-table thead tr { background:#166534; }
.data-table thead th { padding:7px 9px; text-align:left; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:.5px; color:#fff; border-right:1px solid #15803d; }
.data-table thead th:last-child { border-right:none; }
.data-table tbody tr:nth-child(odd)  { background:#fff; }
.data-table tbody tr:nth-child(even) { background:#f8fafc; }
.data-table tbody td { padding:7px 9px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.data-table tbody tr:last-child td { border-bottom:none; }
.table-wrap { border:1px solid #dcfce7; border-radius:0 0 6px 6px; overflow:hidden; }

/* ─── Badges ─── */
.badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:8px; font-weight:bold; }
.badge-aprobado    { background:#dcfce7; color:#166534; }
.badge-pendiente   { background:#fef9c3; color:#854d0e; }
.badge-en_revision { background:#dbeafe; color:#1e40af; }
.badge-rechazado   { background:#fee2e2; color:#991b1b; }

/* ─── Vacío ─── */
.empty { text-align:center; padding:28px; color:#94a3b8; font-style:italic; font-size:11px; border:1px solid #dcfce7; border-top:none; border-radius:0 0 6px 6px; }

/* ─── Footer ─── */
.footer { margin-top:20px; border-top:2px solid #dcfce7; padding-top:8px; display:table; width:100%; font-size:8px; color:#94a3b8; }
.footer-left  { display:table-cell; }
.footer-right { display:table-cell; text-align:right; }
.footer-center { display:table-cell; text-align:center; color:#39A900; font-weight:bold; }
</style>
</head>
<body>

@php
    $logoPath = public_path('images/sena-logo.png');
    $logoB64  = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $tipoLabel = match($tipo) {
        'aprobados'  => 'Solo Productos Aprobados',
        'internos'   => 'Productos Internos del Grupo',
        'semilleros' => 'Productos de Semilleros',
        default      => 'Todos los Productos',
    };
@endphp

{{-- ══ CABECERA INSTITUCIONAL ══ --}}
<div class="header-wrap">
    <div style="display:table; width:100%;">
        <div class="header-logo-cell">
            @if($logoB64)
                <img src="{{ $logoB64 }}" alt="SENA">
            @endif
        </div>
        <div class="header-org-cell" style="padding-left:14px;">
            <div class="org-name">SENA — Servicio Nacional de Aprendizaje</div>
            <div class="org-sub">República de Colombia &nbsp;·&nbsp; Ministerio del Trabajo</div>
            <div class="org-dep">Grupo de Investigación &nbsp;·&nbsp; Sistema de Gestión Documental</div>
        </div>
        <div class="header-doc-cell">
            <div class="doc-label">Informe de Investigación</div>
            <div class="doc-sub">Investigador Asociado</div>
            <div style="font-size:8px; color:#39A900; font-weight:bold; margin-top:3px;">Código: SGD-INV-{{ now()->format('Ymd') }}-{{ auth()->id() }}</div>
        </div>
    </div>
</div>
<div class="band"></div>

{{-- ══ METADATA DEL REPORTE ══ --}}
<div class="report-info">
    <table style="width:100%">
        <tr>
            <td class="ri-cell">
                <div class="ri-label">Tipo de reporte</div>
                <div class="ri-value">{{ $tipoLabel }}</div>
            </td>
            <td class="ri-cell">
                <div class="ri-label">Período</div>
                <div class="ri-value">{{ $etiqueta }}</div>
            </td>
            <td class="ri-cell">
                <div class="ri-label">Investigador</div>
                <div class="ri-value">{{ auth()->user()->name }}</div>
            </td>
            <td class="ri-cell">
                <div class="ri-label">Fecha de generación</div>
                <div class="ri-value">{{ now()->format('d/m/Y H:i') }}</div>
            </td>
            <td class="ri-cell" style="padding-right:0;">
                <div class="ri-label">Total registros</div>
                <div class="ri-value">{{ $productos->count() }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

{{-- ══ MÉTRICAS ══ --}}
<div class="sec-title">Resumen de Actividad</div>
@php
    $aprobados  = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'aprobado')->count();
    $revision   = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'en_revision')->count();
    $pendientes = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'pendiente')->count();
    $rechazados = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'rechazado')->count();
@endphp
<div class="table-wrap" style="border-radius:0 0 6px 6px; padding:10px 8px; margin-bottom:4px;">
    <table class="metrics-table">
        <tr>
            <td class="metric-cell m-default">
                <div class="m-num">{{ $metricas['total_proyectos'] }}</div>
                <div class="m-label">Proyectos Creados</div>
            </td>
            <td class="metric-cell m-default">
                <div class="m-num">{{ $productos->count() }}</div>
                <div class="m-label">Total Productos</div>
            </td>
            <td class="metric-cell m-green">
                <div class="m-num">{{ $aprobados }}</div>
                <div class="m-label">Aprobados</div>
            </td>
            <td class="metric-cell m-blue">
                <div class="m-num">{{ $revision }}</div>
                <div class="m-label">En Revisión</div>
            </td>
            <td class="metric-cell m-amber">
                <div class="m-num">{{ $pendientes }}</div>
                <div class="m-label">Pendientes</div>
            </td>
            <td class="metric-cell m-red">
                <div class="m-num">{{ $rechazados }}</div>
                <div class="m-label">Rechazados</div>
            </td>
        </tr>
    </table>
</div>

{{-- ══ TABLA DE PRODUCTOS ══ --}}
<div class="sec-title" style="margin-top:14px;">
    Detalle de Productos
    &nbsp;<span style="font-weight:normal; font-size:8px; opacity:.85;">({{ $tipoLabel }} · {{ $etiqueta }} · {{ $productos->count() }} registros)</span>
</div>

@if($productos->isNotEmpty())
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:22px;">#</th>
                <th>Título del Producto</th>
                <th>Proyecto Asociado</th>
                <th style="width:36px;">Año</th>
                <th style="width:72px;">Estado</th>
                <th>Tipología Minciencias</th>
                <th>Área del Conocimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $i => $p)
            @php
                $estado = $p->estado_revision?->value ?? (string)($p->estado_revision ?? 'pendiente');
            @endphp
            <tr>
                <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                <td><strong style="color:#0f172a;">{{ $p->titulo }}</strong></td>
                <td style="color:#64748b;">{{ $p->product?->project?->nombre ?? '—' }}</td>
                <td style="text-align:center; color:#475569; font-weight:bold;">{{ $p->anio_publicacion ?? '—' }}</td>
                <td><span class="badge badge-{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</span></td>
                <td style="color:#64748b;">{{ $p->mincienciasTypology?->nombre ?? '—' }}</td>
                <td style="color:#64748b;">{{ $p->knowledgeArea?->nombre ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="empty">No se encontraron productos con los filtros aplicados.</div>
@endif

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <div class="footer-left">
        SENA · Servicio Nacional de Aprendizaje · {{ now()->year }}<br>
        <span style="color:#bbb;">Documento de uso interno. Sistema de Gestión Documental — SGD.</span>
    </div>
    <div class="footer-center">sgd.sena.gov.co</div>
    <div class="footer-right">
        Generado el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}<br>
        <span style="color:#bbb;">Página 1 de 1</span>
    </div>
</div>

</div>{{-- /body --}}
</body>
</html>
