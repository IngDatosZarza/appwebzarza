<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenido a La Zarza Contigo</title>
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
            background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%);
            padding: 40px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        .header p {
            color: rgba(255,255,255,0.85);
            font-size: 15px;
            margin: 0;
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
        .features {
            background: #faf5ff;
            border-left: 4px solid #b51a8a;
            border-radius: 0 8px 8px 0;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .features ul {
            margin: 0;
            padding: 0 0 0 18px;
            color: #555555;
            font-size: 15px;
            line-height: 1.9;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            margin: 8px 0 24px 0;
        }
        .qr-section {
            text-align: center;
            padding: 24px 0;
            border-top: 1px solid #f0e6ff;
            border-bottom: 1px solid #f0e6ff;
            margin: 24px 0;
        }
        .qr-section p {
            font-size: 14px;
            color: #888;
            margin-bottom: 12px;
        }
        .qr-code {
            font-size: 13px;
            font-family: monospace;
            background: #f4f4f7;
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            color: #71398d;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .footer {
            background: #f4f4f7;
            padding: 24px 32px;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #999999;
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>🌿 La Zarza Contigo</h1>
                <p>Tu programa de fidelización</p>
            </div>

            <div class="body">
                <p class="greeting">¡Hola, <?php echo e($usuario->nombres); ?>!</p>

                <p class="text">
                    Tu cuenta en <strong>La Zarza Contigo</strong> ha sido creada exitosamente.
                    A partir de ahora formas parte de nuestra comunidad y podrás disfrutar de
                    beneficios exclusivos en nuestras sucursales.
                </p>

                <div class="features">
                    <ul>
                        <li>Acumula beneficios en cada compra</li>
                        <li>Accede a cupones y descuentos exclusivos</li>
                        <li>Identifícate en sucursal con tu QR personal</li>
                        <li>Registra tus tickets de compra desde la app</li>
                    </ul>
                </div>

                <p class="text">
                    Inicia sesión y visita la sección <strong>Mi Tarjeta</strong> para ver
                    tu código QR personal. Preséntalo en cualquier sucursal para que el
                    equipo de La Zarza te identifique.
                </p>

                <div style="text-align:center;">
                    <a href="<?php echo e(url('/login')); ?>" class="btn">Iniciar sesión ahora</a>
                </div>

                <?php if($usuario->qr_codigo): ?>
                <div class="qr-section">
                    <p>Tu código de identificación único</p>
                    <span class="qr-code"><?php echo e($usuario->qr_codigo); ?></span>
                </div>
                <?php endif; ?>

                <p class="text" style="font-size:13px; color:#888;">
                    Si no creaste esta cuenta, ignora este correo.
                </p>
            </div>

            <div class="footer">
                <p>La Zarza Contigo &bull; Programa de Fidelización<br/>
                Este mensaje fue generado automáticamente, por favor no respondas a este correo.</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/emails/welcome.blade.php ENDPATH**/ ?>