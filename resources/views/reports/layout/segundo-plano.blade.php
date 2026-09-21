<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generando Reporte | Hotel Bugambilias</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            padding: 24px;
        }
        .card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #711C37 0%, #a22950 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(113, 28, 55, 0.4);
            animation: pulse 2s infinite ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.06); opacity: 0.9; }
        }
        .icon-wrap svg {
            width: 40px;
            height: 40px;
            color: #ffffff;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }
        .code-tag {
            font-family: monospace;
            font-weight: bold;
            color: #f472b6;
            margin-bottom: 12px;
            display: block;
            font-size: 13px;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        .notice-box {
            background: rgba(113, 28, 55, 0.2);
            border: 1px solid rgba(244, 114, 182, 0.3);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #fbcfe8;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #711C37;
            color: #ffffff;
            border: 1px solid rgba(244, 114, 182, 0.4);
            box-shadow: 0 4px 15px rgba(113, 28, 55, 0.4);
        }
        .btn-primary:hover {
            background: #8b2344;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <span class="badge">Procesamiento en Segundo Plano</span>

        <h1>Generando Reporte</h1>
        @if(!empty($codigo))
            <span class="code-tag">{{ $codigo }}</span>
        @endif

        <p>
            {{ $mensaje ?? 'El reporte contiene un volumen alto de datos y se está procesando en segundo plano para garantizar la estabilidad y rapidez del sistema.' }}
        </p>

        <div class="notice-box">
            <span style="font-size: 18px;">🔔</span>
            <div>
                <strong>Aviso automático en el panel:</strong><br>
                Recibirás una notificación en la <strong>campana de notificaciones</strong> de tu panel en cuanto el archivo PDF esté listo para descargar.
            </div>
        </div>

        <div class="actions">
            <button onclick="window.close();" class="btn btn-secondary">Cerrar Pestaña</button>
            <a href="{{ url('/admin') }}" class="btn btn-primary">Ir al Panel Principal</a>
        </div>
    </div>
</body>
</html>
