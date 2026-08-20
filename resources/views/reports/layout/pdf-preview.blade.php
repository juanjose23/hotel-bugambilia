<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Reporte PDF' }} | Hotel Bugambilias</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/hotel-icon.webp') }}" type="image/svg+xml">
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            background: #111827;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }
    </style>
</head>
<body>
    <iframe src="{{ $pdfUrl }}" title="{{ $titulo ?? 'Reporte PDF' }}"></iframe>
</body>
</html>
