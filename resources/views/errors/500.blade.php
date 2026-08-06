@include('errors.partials.error-page', [
    'codigo' => 500,
    'badge' => 'Error del servidor',
    'titulo' => 'Algo no salió como esperábamos',
    'mensaje' => 'Ocurrió un error inesperado al procesar su solicitud. Nuestro equipo técnico ha sido notificado.',
])
