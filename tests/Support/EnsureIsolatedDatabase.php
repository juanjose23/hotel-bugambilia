<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

final class EnsureIsolatedDatabase
{
    public static function check(Application $app, bool $dusk = false): void
    {
        $connection = $app['config']->get('database.default');
        $config = $app['config']->get('database.connections.'.$connection);
        $database = is_array($config) ? ($config['database'] ?? '') : '';
        $pattern = $dusk ? '/_dusk$/' : '/(^testing$|_testing$)/';
        if ($connection !== 'pgsql' || ! is_string($database) || ! preg_match($pattern, $database) || ! empty($config['url'])) {
            throw new RuntimeException('Las pruebas requieren PostgreSQL aislado: nombre terminado en '.($dusk ? '_dusk' : '_testing').'. Se bloqueó el acceso antes de modificar tablas.');
        }
    }
}
