@include('errors.partials.error-page', [
    'codigo' => 403,
    'badge' => 'Acceso Restringido',
    'titulo' => 'Acceso Prohibido',
    'mensaje' => 'No cuentas con los permisos necesarios para visualizar este contenido o acceder a esta función.',
])
