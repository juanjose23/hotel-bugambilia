<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $datos->title }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 24px 12px;">
        <tr>
            <td align="center">
                <!-- Main Container Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); border: 1px solid #e2e8f0;">
                    
                    <!-- Header with Branding -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #881337 0%, #be123c 100%); padding: 32px 28px; text-align: center;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); padding: 8px 18px; border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.2); margin-bottom: 12px;">
                                            <span style="color: #fef08a; font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;">
                                                Hotel Bugambilias Estelí
                                            </span>
                                        </div>
                                        <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 900; tracking-tight: -0.5px;">
                                            {{ config('app.name', 'Hotel Bugambilias') }}
                                        </h1>
                                        <p style="color: #fecdd3; margin: 4px 0 0 0; font-size: 13px; font-weight: 500;">
                                            Hospitalidad & Confort de Clase Mundial
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Notification Body Content -->
                    <tr>
                        <td style="padding: 32px 28px;">
                            <!-- Category Badge -->
                            <div style="margin-bottom: 16px;">
                                <span style="display: inline-block; background-color: #ffe4e6; color: #9f1239; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $datos->type->getLabel() }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h2 style="color: #0f172a; margin: 0 0 16px 0; font-size: 20px; font-weight: 800; line-height: 1.3;">
                                {{ $datos->title }}
                            </h2>

                            <!-- Body Text Box -->
                            <div style="background-color: #f8fafc; border-left: 4px solid #be123c; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <p style="color: #334155; margin: 0; font-size: 14px; line-height: 1.6; font-weight: 400; whitespace: pre-line;">
                                    {{ $datos->body }}
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            @if (! empty($datos->actions))
                                <div style="margin-top: 24px; text-align: center;">
                                    @foreach ($datos->actions as $action)
                                        @if (method_exists($action, 'getUrl') && $action->getUrl() !== null)
                                            <a href="{{ $action->getUrl() }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #be123c 0%, #9f1239 100%); color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 50px; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.25); margin: 6px 4px;">
                                                {{ $action->getLabel() }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div style="margin-top: 24px; text-align: center;">
                                    <a href="{{ route('mis-reservas') }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #be123c 0%, #9f1239 100%); color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 50px; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.25); margin: 6px 4px;">
                                        Ver Portal de Huéspedes
                                    </a>
                                </div>
                            @endif

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 24px 28px; text-align: center; color: #94a3b8; font-size: 12px; line-height: 1.6;">
                            <p style="margin: 0 0 8px 0; color: #f8fafc; font-weight: 700;">
                                Hotel Bugambilias Estelí, Nicaragua
                            </p>
                            <p style="margin: 0 0 12px 0;">
                                Teléfono: <a href="tel:+50587136805" style="color: #fb7185; text-decoration: none; font-weight: 600;">+505 8713 6805</a> | Email: <a href="mailto:contacto@hotelbugambilia.com" style="color: #fb7185; text-decoration: none; font-weight: 600;">contacto@hotelbugambilia.com</a>
                            </p>
                            <div style="border-top: 1px solid #1e293b; padding-top: 12px; margin-top: 12px;">
                                <p style="margin: 0; font-size: 11px; color: #64748b;">
                                    © {{ date('Y') }} Hotel Bugambilias. Todos los derechos reservados.
                                </p>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

