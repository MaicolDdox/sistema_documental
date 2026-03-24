<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Grupo de Investigación — SENA</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1e293b; background:#fff; }

/* ─── Cabecera institucional ─── */
.header-wrap { background:#fff; border-bottom:3px solid #39A900; padding:14px 24px 12px; }
.header-table { display:table; width:100%; }
.header-logo-cell { display:table-cell; vertical-align:middle; width:70px; }
.header-logo-cell img { width:56px; height:auto; }
.header-org-cell { display:table-cell; vertical-align:middle; padding-left:14px; }
.org-name { font-size:12px; font-weight:bold; color:#166534; line-height:1.2; }
.org-sub  { font-size:8px; color:#64748b; margin-top:2px; }
.org-dep  { font-size:8px; color:#39A900; font-weight:bold; margin-top:1px; }
.header-doc-cell { display:table-cell; vertical-align:middle; text-align:right; width:210px; }
.doc-label { font-size:15px; font-weight:bold; color:#0f172a; }
.doc-sub   { font-size:8px; color:#64748b; margin-top:2px; }
.doc-code  { font-size:8px; color:#39A900; font-weight:bold; margin-top:3px; }

/* ─── Banda ─── */
.band { height:5px; background:linear-gradient(90deg, #166534 0%, #39A900 50%, #86efac 100%); }

/* ─── Info reporte ─── */
.report-info { background:#f0fdf4; border:1px solid #dcfce7; border-radius:6px; padding:10px 16px; margin:14px 24px; }

/* ─── Cuerpo ─── */
.body { padding:0 24px 24px; }

/* ─── Título sección ─── */
.sec-title { font-size:10px; font-weight:bold; color:#fff; background:#166534; padding:6px 12px; border-radius:4px 4px 0 0; text-transform:uppercase; letter-spacing:.7px; margin-top:16px; }

/* ─── Métricas ─── */
.metrics-table { width:100%; border-collapse:separate; border-spacing:6px; }
.metric-cell { padding:10px 12px; border-radius:6px; text-align:center; }
.m-num   { font-size:26px; font-weight:bold; line-height:1; }
.m-label { font-size:8px; text-transform:uppercase; letter-spacing:.5px; margin-top:3px; }
.m-default { background:#f8fafc; border:1px solid #e2e8f0; }
.m-default .m-num { color:#0f172a; }
.m-default .m-label { color:#64748b; }
.m-green { background:#f0fdf4; border:1px solid #bbf7d0; }
.m-green .m-num { color:#15803d; } .m-green .m-label { color:#166534; }
.m-amber { background:#fffbeb; border:1px solid #fde68a; }
.m-amber .m-num { color:#b45309; } .m-amber .m-label { color:#92400e; }
.m-red   { background:#fef2f2; border:1px solid #fecaca; }
.m-red   .m-num { color:#dc2626; } .m-red .m-label { color:#991b1b; }
.m-blue  { background:#eff6ff; border:1px solid #bfdbfe; }
.m-blue  .m-num { color:#1d4ed8; } .m-blue .m-label { color:#1e40af; }

/* ─── Tabla ─── */
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

/* ─── Footer ─── */
.footer { margin-top:20px; border-top:2px solid #dcfce7; padding-top:8px; display:table; width:100%; font-size:8px; color:#94a3b8; }
.footer-left   { display:table-cell; }
.footer-center { display:table-cell; text-align:center; color:#39A900; font-weight:bold; }
.footer-right  { display:table-cell; text-align:right; }
.empty { text-align:center; padding:28px; color:#94a3b8; font-style:italic; border:1px solid #dcfce7; border-top:none; border-radius:0 0 6px 6px; }
</style>
</head>
<body>

@php
    $logoPath = public_path('images/sena-logo.png');
    $logoB64  = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    $tipoLabel = match($tipo ?? 'general') {
        'por_investigador' => 'Por Investigador',
        'por_anio'         => 'Por Año de Publicación',
        default            => 'Reporte General Detallado',
    };

    $aprobados  = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'aprobado')->count();
    $pendientes = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'pendiente')->count();
    $revision   = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'en_revision')->count();
    $rechazados = $productos->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'rechazado')->count();
@endphp

{{-- ══ CABECERA ══ --}}
<div class="header-wrap">
    <div class="header-table">
        <div class="header-logo-cell">
            @if($logoB64)
                <img src="{{ $logoB64 }}" alt="SENA">
            @endif
        </div>
        <div class="header-org-cell">
            <div class="org-name">SENA — Servicio Nacional de Aprendizaje</div>
            <div class="org-sub">República de Colombia &nbsp;·&nbsp; Ministerio del Trabajo</div>
            <div class="org-dep">Grupo de Investigación &nbsp;·&nbsp; Sistema de Gestión Documental</div>
        </div>
        <div class="header-doc-cell">
            <div class="doc-label">Informe del Grupo</div>
            <div class="doc-sub">Director de Investigación</div>
            <div class="doc-code">Código: SGD-GRP-{{ now()->format('Ymd') }}</div>
        </div>
    </div>
</div>
<div class="band"></div>

{{-- ══ INFO REPORTE ══ --}}
<div class="report-info">
    <table style="width:100%;">
        <tr>
            <td style="padding-right:16px;">
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Tipo de reporte</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ $tipoLabel }}</div>
            </td>
            <td style="padding-right:16px;">
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Período</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ $etiqueta }}</div>
            </td>
            <td style="padding-right:16px;">
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Investigadores</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ $actividadGeneral['total_investigadores'] }}</div>
            </td>
            <td style="padding-right:16px;">
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Fecha generación</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ now()->format('d/m/Y H:i') }}</div>
            </td>
            <td>
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Total registros</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ $productos->count() }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

{{-- ══ MÉTRICAS ══ --}}
<div class="sec-title">Resumen del Grupo de Investigación</div>
<div class="table-wrap" style="border-radius:0 0 6px 6px; padding:10px 6px;">
    <table class="metrics-table">
        <tr>
            <td class="metric-cell m-default">
                <div class="m-num">{{ $actividadGeneral['total_investigadores'] }}</div>
                <div class="m-label">Investigadores</div>
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

{{-- ══ VISTA SEGÚN TIPO ══ --}}

@if(($tipo ?? 'general') === 'por_investigador')

    {{-- Agrupado por investigador --}}
    <div class="sec-title" style="margin-top:14px;">Productos por Investigador <span style="font-weight:normal; font-size:8px;">({{ $etiqueta }})</span></div>
    @php $agrupado = $productos->groupBy(fn($p) => $p->author?->person?->nombre_completo ?? $p->author?->email ?? 'Desconocido'); @endphp
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Investigador</th>
                    <th style="width:60px; text-align:center;">Total Prod.</th>
                    <th style="width:60px; text-align:center;">Aprobados</th>
                    <th style="width:60px; text-align:center;">Pendientes</th>
                    <th style="width:60px; text-align:center;">Rechazados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agrupado as $nombre => $prods)
                @php
                    $apr = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'aprobado')->count();
                    $pen = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'pendiente')->count();
                    $rec = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'rechazado')->count();
                @endphp
                <tr>
                    <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                    <td><strong style="color:#0f172a;">{{ $nombre }}</strong></td>
                    <td style="text-align:center; font-weight:bold; color:#0f172a;">{{ $prods->count() }}</td>
                    <td style="text-align:center;"><span class="badge badge-aprobado">{{ $apr }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-pendiente">{{ $pen }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-rechazado">{{ $rec }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@elseif(($tipo ?? 'general') === 'por_anio')

    {{-- Agrupado por año --}}
    <div class="sec-title" style="margin-top:14px;">Productos por Año de Publicación</div>
    @php $porAnio = $productos->groupBy('anio_publicacion')->sortKeysDesc(); @endphp
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:70px;">Año</th>
                    <th style="width:60px; text-align:center;">Total</th>
                    <th style="width:65px; text-align:center;">Aprobados</th>
                    <th style="width:65px; text-align:center;">Pendientes</th>
                    <th style="width:65px; text-align:center;">Rechazados</th>
                    <th>Investigadores</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porAnio as $anio => $prods)
                @php
                    $apr = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'aprobado')->count();
                    $pen = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'pendiente')->count();
                    $rec = $prods->filter(fn($p) => ($p->estado_revision?->value ?? $p->estado_revision) === 'rechazado')->count();
                    $invs = $prods->pluck('author.email')->unique()->filter()->implode(', ');
                @endphp
                <tr>
                    <td style="font-weight:bold; color:#0f172a;">{{ $anio ?: '—' }}</td>
                    <td style="text-align:center; font-weight:bold;">{{ $prods->count() }}</td>
                    <td style="text-align:center;"><span class="badge badge-aprobado">{{ $apr }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-pendiente">{{ $pen }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-rechazado">{{ $rec }}</span></td>
                    <td style="font-size:8px; color:#64748b;">{{ Str::limit($invs, 60) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@else

    {{-- Reporte general --}}
    <div class="sec-title" style="margin-top:14px;">
        Detalle General de Productos
        <span style="font-weight:normal; font-size:8px;">({{ $etiqueta }} · {{ $productos->count() }} registros)</span>
    </div>
    @if($productos->isNotEmpty())
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>Investigador</th>
                    <th>Título del Producto</th>
                    <th>Proyecto</th>
                    <th style="width:36px;">Año</th>
                    <th style="width:72px;">Estado</th>
                    <th>Tipología</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $i => $p)
                @php $estado = $p->estado_revision?->value ?? (string)($p->estado_revision ?? 'pendiente'); @endphp
                <tr>
                    <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                    <td style="color:#64748b; font-size:8px;">{{ $p->author?->person?->nombre_completo ?? $p->author?->email ?? '—' }}</td>
                    <td><strong style="color:#0f172a;">{{ $p->titulo }}</strong></td>
                    <td style="color:#64748b; font-size:8px;">{{ $p->product?->project?->nombre ?? '—' }}</td>
                    <td style="text-align:center; font-weight:bold; color:#475569;">{{ $p->anio_publicacion ?? '—' }}</td>
                    <td><span class="badge badge-{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</span></td>
                    <td style="color:#64748b; font-size:8px;">{{ $p->mincienciasTypology?->nombre ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No se encontraron productos con los filtros seleccionados.</div>
    @endif

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
