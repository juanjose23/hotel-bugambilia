@php
    $codigo = $codigo ?? 500;
    $badge = $badge ?? 'Error de sistema';
    $titulo = $titulo ?? 'Error interno del servidor';
    $mensaje = $mensaje ?? 'Ocurrió un error inesperado al procesar su solicitud. Por favor, inténtelo de nuevo en unos minutos.';
    $logo = \App\Support\HotelInfo::getLogoBase64();
    $nombreHotel = (string) config('hotel.name', 'Hotel Bugambilias');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $codigo }} - {{ $badge }} | {{ $nombreHotel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body {
            background-color: #ffffff;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        .glow {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(162, 28, 175, 0.15) 0%, rgba(147, 51, 234, 0.1) 50%, rgba(255, 255, 255, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .card-container {
            position: relative;
            z-index: 10;
            max-width: 36rem;
            width: 100%;
            text-align: center;
        }
        .card {
            background: rgba(250, 250, 250, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.12);
            padding: 2.5rem 2rem;
        }
        .logo {
            height: 3rem;
            width: auto;
            margin: 0 auto 1.5rem;
            display: block;
            object-fit: contain;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            margin-bottom: 1.25rem;
        }
        .badge-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #a21caf, #6b21a8);
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #475569;
            margin: 0 auto 2rem;
            max-width: 28rem;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
        }
        @media (min-width: 640px) {
            .actions { flex-direction: row; }
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            border: 1px solid transparent;
        }
        @media (min-width: 640px) { .btn { width: auto; } }
        .btn-primary {
            background: linear-gradient(135deg, #a21caf 0%, #701a75 100%);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(162, 28, 175, 0.25);
        }
        .btn-primary:hover {
            opacity: 0.92;
            box-shadow: 0 14px 32px rgba(162, 28, 175, 0.35);
        }
        .btn-secondary {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #334155;
        }
        .btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; }
        .footer-note {
            margin-top: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #94a3b8;
        }

        @media (prefers-color-scheme: dark) {
            body { background-color: #0d0e12; color: #f8fafc; }
            .card {
                background: rgba(18, 20, 26, 0.85);
                border-color: rgba(255, 255, 255, 0.1);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            }
            .badge { background: #1e293b; border-color: rgba(255, 255, 255, 0.1); color: #cbd5e1; }
            h1 { color: #ffffff; }
            p { color: #cbd5e1; }
            .btn-secondary { background: #1e293b; border-color: rgba(255, 255, 255, 0.1); color: #f1f5f9; }
            .btn-secondary:hover { background: #334155; }
            .footer-note { color: #64748b; }
        }
    </style>
</head>
<body>
    <div class="glow"></div>
    <div class="card-container">
        <main class="card">
            @if ($logo !== '')
                <img class="logo" src="{{ $logo }}" alt="{{ $nombreHotel }}">
            @endif
            <div class="badge">
                <span class="badge-dot"></span>
                Error {{ $codigo }} • {{ $badge }}
            </div>
            <h1>{{ $titulo }}</h1>
            <p>{{ $mensaje }}</p>
            <div class="actions">
                <a class="btn btn-primary" href="/">Ir al inicio</a>
                <a class="btn btn-secondary" href="javascript:location.reload()">Reintentar</a>
                <a class="btn btn-secondary" href="javascript:history.back()">Volver atrás</a>
            </div>
        </main>
        <div class="footer-note">
            {{ $nombreHotel }} — Servicio y Confort
        </div>
    </div>
</body>
</html>
