@include('errors.partials.error-page', [
    'codigo' => 404,
    'badge' => 'Página no encontrada',
    'titulo' => '¿Te has perdido?',
    'mensaje' => 'La página que estás buscando no existe, ha sido movida o la dirección ingresada es incorrecta.',
])
