<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

$mode = $argv[1] ?? 'testing';
if (! in_array($mode, ['testing', 'dusk'], true)) {
    throw new RuntimeException('Entorno de prueba desconocido.');
}
$root = dirname(__DIR__);
$file = $mode === 'dusk' ? (is_file($root.'/.env.dusk.local') ? '.env.dusk.local' : '.env.dusk.example') : '.env.testing';
$values = Dotenv\Dotenv::parse((string) file_get_contents($root.'/'.$file));
if ($mode === 'testing' && is_file($root.'/.env.testing.local')) {
    $values = array_replace($values, Dotenv\Dotenv::parse((string) file_get_contents($root.'/.env.testing.local')));
}
$environment = [];
foreach ($values as $key => $value) {
    $environment[$key] = getenv($key) !== false ? getenv($key) : $value;
}
foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_URL', 'APP_KEY', 'DUSK_DRIVER_URL', 'DUSK_EXTERNAL_SERVER', 'BROWSER'] as $key) {
    if (getenv($key) !== false) {
        $environment[$key] = getenv($key);
    }
}
$environment['APP_ENV'] = $mode === 'dusk' ? 'dusk.local' : 'testing';
$environment['DB_URL'] = '';
$environment['APP_CONFIG_CACHE'] = 'storage/framework/testing/config-'.$mode.'.php';
$environment['APP_SERVICES_CACHE'] = 'storage/framework/testing/services-'.$mode.'.php';
$environment['APP_PACKAGES_CACHE'] = 'storage/framework/testing/packages-'.$mode.'.php';
$environment['APP_ROUTES_CACHE'] = 'storage/framework/testing/routes-'.$mode.'.php';
$environment['VIEW_COMPILED_PATH'] = $root.'/storage/framework/testing/views-'.$mode;
$environment['MAIL_MAILER'] = 'array';
$environment['QUEUE_CONNECTION'] = 'sync';
$environment['CACHE_STORE'] = 'array';
$environment['SESSION_DRIVER'] = $mode === 'dusk' ? 'file' : 'array';
$environment['COCINA_DEMO'] = 'false';
$environment['TESORERIA_HABILITADO'] = 'false';
$database = (string) ($environment['DB_DATABASE'] ?? '');
if (($environment['DB_CONNECTION'] ?? '') !== 'pgsql' || ! preg_match($mode === 'dusk' ? '/_dusk$/' : '/(^testing$|_testing$)/', $database)) {
    throw new RuntimeException('Se requiere una base PostgreSQL dedicada terminada en '.($mode === 'dusk' ? '_dusk' : '_testing').'. No se ejecutaron migraciones.');
}
if (empty($environment['APP_KEY'])) {
    throw new RuntimeException('Define APP_KEY en el entorno de pruebas.');
}
@mkdir($environment['VIEW_COMPILED_PATH'], 0775, true);
echo json_encode($environment, JSON_THROW_ON_ERROR);
