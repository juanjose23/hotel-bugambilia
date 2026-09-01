<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Hotel Bugambilias — Definición Modular de Rutas Web
|--------------------------------------------------------------------------
|
| Las rutas de la aplicación están organizadas modularmente por dominio:
| - public.php: Carga las rutas públicas organizadas en routes/public/
| - admin.php: Carga las rutas administrativas organizadas en routes/admin/
|
*/

require __DIR__.'/public.php';
require __DIR__.'/admin.php';
