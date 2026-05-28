<?php

namespace Tests;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\User;
use App\UseCases\Limpieza\Mutations\RegistrarSolicitudLimpieza;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Catalogo $categoria
 * @property Ubicacion $ubicacion
 * @property RegistrarSolicitudLimpieza $useCase
 * @property User $user
 */
abstract class TestCase extends BaseTestCase
{
    //
}
