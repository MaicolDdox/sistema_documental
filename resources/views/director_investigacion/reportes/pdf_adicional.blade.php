<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Documental — SENA</title>
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
.badge-activo      { background:#dcfce7; color:#166534; }
.badge-inactivo    { background:#fee2e2; color:#991b1b; }

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

    $tipoLabel = match($tipo) {
        'por_proyecto' => 'Lista de Proyectos',
        'por_macroproyecto' => 'Lista de Macroproyectos',
        'investigadores_detalle' => 'Detalle de Investigadores',
        default => 'Reporte Generado',
    };
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
            <div class="doc-code">Código: SGD-GRP-{{ now('America/Bogota')->format('Ymd') }}</div>
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
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Investigadores en el Grupo</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ $actividadGeneral['total_investigadores'] }}</div>
            </td>
            <td style="padding-right:16px;">
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Fecha generación</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ now('America/Bogota')->format('d/m/Y H:i') }}</div>
            </td>
            <td>
                <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#64748b;letter-spacing:.5px;margin-bottom:2px;">Total registros</div>
                <div style="font-size:10px;font-weight:bold;color:#166534;">{{ count($data) }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

@if($tipo === 'por_proyecto')
    {{-- ══ VISTA DE PROYECTOS ══ --}}
    <div class="sec-title" style="margin-top:14px;">Detalle de Proyectos del Grupo</div>
    @if(count($data) > 0)
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>Nombre del Proyecto</th>
                    <th>Línea de Investigación</th>
                    <th>Macroproyecto</th>
                    <th>Creador</th>
                    <th style="width:60px; text-align:center;">Estado</th>
                    <th style="width:60px; text-align:center;">Inicio</th>
                    <th style="width:60px; text-align:center;">Fin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $p)
                <tr>
                    <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                    <td><strong style="color:#0f172a;">{{ $p->nombre }}</strong></td>
                    <td style="color:#64748b;">{{ $p->linea_investigacion ?? '—' }}</td>
                    <td style="color:#64748b;">{{ $p->macroproyect?->nombre ?? '—' }}</td>
                    <td style="color:#64748b; font-size:8px;">{{ $p->creator?->person?->nombre_completo ?? $p->creator?->email ?? '—' }}</td>
                    <td style="text-align:center;"><span class="badge badge-{{ $p->estado === 'activo' ? 'activo' : 'inactivo' }}">{{ ucfirst($p->estado) }}</span></td>
                    <td style="text-align:center; color:#475569;">{{ $p->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                    <td style="text-align:center; color:#475569;">{{ $p->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No se encontraron proyectos activos para este grupo.</div>
    @endif

@elseif($tipo === 'por_macroproyecto')
    {{-- ══ VISTA DE MACROPROYECTOS ══ --}}
    <div class="sec-title" style="margin-top:14px;">Detalle de Macroproyectos Asociados</div>
    @if(count($data) > 0)
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th style="width:80px;">Código</th>
                    <th>Nombre del Macroproyecto</th>
                    <th>Línea de Programación</th>
                    <th style="width:100px; text-align:center;">Proyectos Vinculados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $m)
                <tr>
                    <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                    <td style="font-weight:bold; color:#475569;">{{ $m->codigo }}</td>
                    <td><strong style="color:#0f172a;">{{ $m->nombre }}</strong></td>
                    <td style="color:#64748b;">{{ $m->linea_programacion ?? '—' }}</td>
                    <td style="text-align:center; font-weight:bold; color:#0f172a;">{{ $m->proyectos_count ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No hay macroproyectos asociados al grupo.</div>
    @endif

@elseif($tipo === 'investigadores_detalle')
    {{-- ══ VISTA DE INVESTIGADORES DETALLADOS ══ --}}
    <div class="sec-title" style="margin-top:14px;">Productividad Detallada por Investigador</div>
    @if(count($data) > 0)
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>Investigador</th>
                    <th style="width:50px; text-align:center;">Total</th>
                    <th style="width:50px; text-align:center;">Aprob.</th>
                    <th style="width:50px; text-align:center;">Rev.</th>
                    <th style="width:50px; text-align:center;">Pend.</th>
                    <th style="width:50px; text-align:center;">Rech.</th>
                    <th style="width:50px; text-align:center;">Estado</th>
                    <th>CvLAC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $inv)
                <tr>
                    <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                    <td>
                        <strong style="color:#0f172a;">{{ $inv['nombre'] }}</strong><br>
                        <span style="font-size:8px; color:#64748b;">{{ $inv['email'] }}</span>
                    </td>
                    <td style="text-align:center; font-weight:bold; color:#0f172a;">{{ $inv['total'] }}</td>
                    <td style="text-align:center;"><span class="badge badge-aprobado" style="padding:1px 5px;">{{ $inv['aprobados'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-en_revision" style="padding:1px 5px;">{{ $inv['en_revision'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-pendiente" style="padding:1px 5px;">{{ $inv['pendientes'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-rechazado" style="padding:1px 5px;">{{ $inv['rechazados'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-{{ $inv['estado'] === 'activo' ? 'activo' : 'inactivo' }}">{{ $inv['estado'] === 'activo' ? 'Activo' : 'Inact.' }}</span></td>
                    <td style="color:#0ea5e9; font-size:8px;">{{ Str::limit($inv['cvlac'] ?? '—', 35) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No hay investigadores con datos en el grupo.</div>
    @endif

@endif

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <div class="footer-left">
        SENA · Servicio Nacional de Aprendizaje · {{ now('America/Bogota')->year }}<br>
        <span style="color:#bbb;">Documento de uso interno. Sistema de Gestión Documental — SGD.</span>
    </div>
    <div class="footer-center">sgd.sena.gov.co</div>
    <div class="footer-right">
        Generado el {{ now('America/Bogota')->format('d/m/Y \a \l\a\s H:i:s') }}<br>
        <span style="color:#bbb;">Página 1 de 1</span>
    </div>
</div>

</div>{{-- /body --}}
</body>
</html>
