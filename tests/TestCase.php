<?php

declare(strict_types=1);

namespace Tests;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\EnsureIsolatedDatabase;

/**
 * @property Catalogo $categoria
 * @property Ubicacion $ubicacion
 * @property User $user
 */
abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        EnsureIsolatedDatabase::check($app);

        return $app;
    }
}
