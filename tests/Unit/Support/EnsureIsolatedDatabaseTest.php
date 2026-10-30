<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Tests\Support\EnsureIsolatedDatabase;

function aplicacionAislamientoPruebas(string $database, ?string $url = null): Application
{
    $app = new Application(dirname(__DIR__, 3));
    $app->instance('config', new Repository(['database' => ['default' => 'pgsql', 'connections' => ['pgsql' => ['database' => $database, 'url' => $url]]]]));

    return $app;
}

test('bloquea bases operativas antes de permitir pruebas destructivas', function (string $database, bool $dusk, ?string $url): void {
    expect(fn () => EnsureIsolatedDatabase::check(aplicacionAislamientoPruebas($database, $url), $dusk))->toThrow(RuntimeException::class);
})->with([
    ['hotel_bugambilias', false, null],
    ['hotel_bugambilias', true, null],
    ['hotel_testing', true, null],
    ['hotel_dusk', false, null],
    ['hotel_testing', false, 'postgres://localhost/hotel_bugambilias'],
]);

test('permite solamente el destino de pruebas correspondiente', function (string $database, bool $dusk): void {
    EnsureIsolatedDatabase::check(aplicacionAislamientoPruebas($database), $dusk);
    expect(true)->toBeTrue();
})->with([['hotel_testing', false], ['testing', false], ['hotel_dusk', true]]);
