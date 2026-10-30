@php
    $codigo = 410;
    $badge = 'Documento Expirado';
    $titulo = 'Enlace de Reporte Expirado';
    $mensaje = $mensaje ?? 'El enlace temporal de este reporte ha superado su tiempo de validez y el archivo fue eliminado automáticamente del servidor por seguridad. Por favor genere una nueva copia desde el panel de reportes.';
    $logo = \App\Support\Barcode\Support\HotelInfo::getLogoBase64();
    $nombreHotel = (string) config('hotel.name', 'Hotel Bugambilias');
@endphp
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>410 - Reporte Expirado | {{ $nombreHotel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f8fafc;
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
            background: radial-gradient(circle, rgba(234, 88, 12, 0.12) 0%, rgba(249, 115, 22, 0.06) 50%, rgba(255, 255, 255, 0) 70%);
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08);
            padding: 2.75rem 2rem;
        }

        .logo {
            height: 3.25rem;
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
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: #fff7ed;
            border: 1px solid #ffedd5;
            color: #c2410c;
            margin-bottom: 1.25rem;
        }

        .badge-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: #ea580c;
        }

        h1 {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        p {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #64748b;
            margin: 0 auto 2rem;
            max-width: 29rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
        }

        @media (min-width: 640px) {
            .actions {
                flex-direction: row;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem 1.6rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        @media (min-width: 640px) {
            .btn {
                width: auto;
            }
        }

        .btn-primary {
            background: linear-gradient(135deg, #a21caf 0%, #701a75 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(162, 28, 175, 0.25);
        }

        .btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(162, 28, 175, 0.35);
        }

        .btn-secondary {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .footer-note {
            margin-top: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.05em;
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
            Seguridad • {{ $badge }}
        </div>
        <h1>{{ $titulo }}</h1>
        <p>{{ $mensaje }}</p>
        <div class="actions">
            <a class="btn btn-primary" href="/admin">Ir al Panel de Reportes</a>
            <a class="btn btn-secondary" href="javascript:window.close()">Cerrar Pestaña</a>
        </div>
    </main>
    <div class="footer-note">
        {{ $nombreHotel }} — Sistema de Gestión Hotelera
    </div>
</div>
</body>
</html>
