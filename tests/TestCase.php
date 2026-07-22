<?php

namespace Tests;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Catalogo $categoria
 * @property Ubicacion $ubicacion
 * @property User $user
 */
abstract class TestCase extends BaseTestCase
{
    //
}
