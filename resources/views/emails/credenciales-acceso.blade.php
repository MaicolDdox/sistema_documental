<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de acceso — SIGESI</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #39A900; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 0.5px; }
        .header p { color: #d4f0b8; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 36px 40px; color: #374151; }
        .body p { line-height: 1.7; font-size: 15px; margin: 0 0 16px; }
        .credentials { background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .credentials table { width: 100%; border-collapse: collapse; }
        .credentials td { padding: 8px 0; font-size: 15px; }
        .credentials td:first-child { color: #6b7280; width: 160px; font-weight: 600; }
        .credentials td:last-child { color: #111827; font-weight: 700; letter-spacing: 0.3px; }
        .btn { display: inline-block; margin-top: 8px; background-color: #39A900; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .warning { background-color: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 14px 18px; margin: 24px 0; font-size: 13px; color: #92400e; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>SIGESI</h1>
        <p>Plataforma de Investigación y Semilleros SENA</p>
    </div>

    <div class="body">
        <p>Hola, <strong>{{ $usuario->person?->primer_nombre ?? $usuario->email }}</strong>.</p>
        <p>Tu cuenta en el <strong>SIGESI</strong> ha sido creada. A continuación encuentras tus credenciales de acceso:</p>

        <div class="credentials">
            <table>
                <tr>
                    <td>Número de documento:</td>
                    <td>{{ $usuario->numero_documento }}</td>
                </tr>
                <tr>
                    <td>Contraseña temporal:</td>
                    <td>{{ $passwordTemporal }}</td>
                </tr>
                <tr>
                    <td>Correo de contacto:</td>
                    <td>{{ $usuario->email }}</td>
                </tr>
            </table>
        </div>

        <p style="text-align:center">
            <a href="{{ $appUrl }}/login" class="btn">Ingresar al sistema</a>
        </p>

        <div class="warning">
            <strong>⚠ Importante:</strong> Por seguridad, cambia tu contraseña la primera vez que ingreses al sistema desde
            <strong>Configuración → Contraseña</strong>.
        </div>

        <p>Si tienes algún inconveniente para acceder, comunícate con el administrador de tu centro de formación.</p>
        <p>Saludos,<br><strong>Equipo SIGESI — SENA</strong></p>
    </div>

    <div class="footer">
        Este correo fue generado automáticamente por el Sistema SIGESI. Por favor no respondas a este mensaje.
    </div>
</div>
</body>
</html>
