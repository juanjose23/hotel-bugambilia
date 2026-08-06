@include('errors.partials.error-page', [
    'codigo' => 419,
    'badge' => 'Sesión Expirada',
    'titulo' => 'La sesión ha caducado',
    'mensaje' => 'Por motivos de seguridad y tras un periodo de inactividad, tu sesión ha expirado. Por favor, recarga e intentalo de nuevo.',
])
