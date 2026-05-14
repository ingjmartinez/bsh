<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Grupo Joselito</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f3f9;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f3f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #405189 0%, #0ab39c 100%); padding: 30px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">
                                🎉 ¡Bienvenido a Grupo Joselito!
                            </h1>
                            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">
                                Tu cuenta ha sido creada exitosamente
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #495057; font-size: 16px; margin: 0 0 20px; line-height: 1.6;">
                                Hola <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="color: #495057; font-size: 15px; margin: 0 0 25px; line-height: 1.6;">
                                Se ha creado una cuenta para ti en el sistema CRM de <strong>Grupo Joselito</strong>. A continuación encontrarás tus datos de acceso:
                            </p>

                            <!-- Credentials Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f6f9; border-radius: 8px; border-left: 4px solid #405189; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px 25px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <span style="color: #878a99; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Correo electrónico</span><br>
                                                    <span style="color: #405189; font-size: 16px; font-weight: 600;">{{ $user->email }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <span style="color: #878a99; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Contraseña</span><br>
                                                    <span style="color: #405189; font-size: 16px; font-weight: 600;">{{ $plainPassword }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fff3cd; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="color: #856404; font-size: 13px; margin: 0; line-height: 1.5;">
                                            ⚠️ <strong>Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez. No compartas estos datos con nadie.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/login') }}" style="display: inline-block; background: linear-gradient(135deg, #405189 0%, #0ab39c 100%); color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 6px; font-size: 15px; font-weight: 600;">
                                            Iniciar Sesión
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f3f6f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e9ebec;">
                            <p style="color: #878a99; font-size: 12px; margin: 0; line-height: 1.5;">
                                Este es un correo automático generado por el sistema CRM de <strong>Grupo Joselito</strong>.<br>
                                Por favor, no respondas a este correo.
                            </p>
                            <p style="color: #adb5bd; font-size: 11px; margin: 10px 0 0;">
                                &copy; {{ date('Y') }} Grupo Joselito. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
