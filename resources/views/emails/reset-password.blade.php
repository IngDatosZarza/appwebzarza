<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restablecer contraseña - La Zarza Contigo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: #ffffff;
            padding: 32px 32px 0 32px;
            text-align: center;
            border-bottom: 4px solid transparent;
            border-image: linear-gradient(135deg, #71398d 0%, #b51a8a 100%) 1;
        }
        .header-stripe {
            background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%);
            padding: 14px 32px;
            text-align: center;
        }
        .header-stripe p {
            color: rgba(255,255,255,0.92);
            font-size: 14px;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .body {
            padding: 36px 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #71398d;
            margin-bottom: 16px;
        }
        .text {
            font-size: 15px;
            line-height: 1.7;
            color: #555555;
            margin-bottom: 20px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
        }
        .notice-box {
            background: #faf5ff;
            border-left: 4px solid #b51a8a;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            margin: 24px 0;
            font-size: 14px;
            color: #555555;
            line-height: 1.7;
        }
        .url-fallback {
            background: #f4f4f7;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 12px;
            font-family: monospace;
            color: #71398d;
            word-break: break-all;
            margin: 12px 0 0 0;
        }
        .footer {
            background: #f4f4f7;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #999999;
            line-height: 1.6;
        }
        .footer a {
            color: #b51a8a;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <img src="{{ config('app.url') }}/logoZarza.webp" alt="La Zarza Contigo" height="60" style="margin-bottom: 16px;">
            </div>
            <div class="header-stripe">
                <p>Sistema de Fidelización &bull; La Zarza Contigo</p>
            </div>

            <!-- Body -->
            <div class="body">
                <p class="greeting">Hola, {{ $usuario->nombres }} {{ $usuario->apellido_paterno }}</p>

                <p class="text">
                    Recibimos una solicitud para restablecer la contraseña de tu cuenta asociada al correo
                    <strong>{{ $usuario->email }}</strong>.
                </p>

                <p class="text">
                    Haz clic en el botón de abajo para crear una nueva contraseña. Este enlace es válido por
                    <strong>60 minutos</strong>.
                </p>

                <div class="btn-wrapper">
                    <a href="{{ $resetUrl }}" class="btn">Restablecer mi contraseña</a>
                </div>

                <div class="notice-box">
                    <strong>¿No solicitaste este cambio?</strong><br>
                    Si no solicitaste restablecer tu contraseña, ignora este correo. Tu contraseña no cambiará
                    y tu cuenta permanecerá segura.
                </div>

                <p class="text" style="font-size:13px; color:#888;">
                    Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:
                </p>
                <div class="url-fallback">{{ $resetUrl }}</div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin:0 0 6px 0;">
                    &copy; {{ date('Y') }} La Zarza Contigo &bull; Todos los derechos reservados
                </p>
                <p style="margin:0;">
                    Este correo fue enviado automáticamente. Por favor no respondas a este mensaje.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
