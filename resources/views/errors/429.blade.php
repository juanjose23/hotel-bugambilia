@include('errors.partials.error-page', [
    'codigo' => 429,
    'badge' => 'Límite de Peticiones',
    'titulo' => 'Demasiadas solicitudes',
    'mensaje' => 'Has realizado demasiadas peticiones en un periodo corto. Por favor, aguarda unos segundos antes de reintentar.',
])
