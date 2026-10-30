<?php

declare(strict_types=1);

$fail = static function (string $message): never {
    fwrite(STDERR, "Producción: {$message}\n");
    exit(1);
};

if (getenv('APP_ENV') !== 'production') {
    $fail('APP_ENV debe ser production.');
}

if (! in_array(strtolower((string) getenv('APP_DEBUG')), ['false', '0'], true)) {
    $fail('APP_DEBUG debe estar desactivado.');
}

$key = (string) getenv('APP_KEY');
$decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
if (! is_string($decoded) || strlen($decoded) !== 32 || $key === 'base64:n1M8EUQLYLSLBZaUDcTrUqV0aGsChkJHm/K6q38jiAU=') {
    $fail('Configura una APP_KEY propia de 32 bytes; no uses la clave de pruebas.');
}

$url = parse_url((string) getenv('APP_URL'));
if (! is_array($url) || ($url['scheme'] ?? '') !== 'https' || empty($url['host'])) {
    $fail('APP_URL debe ser la dirección HTTPS pública.');
}

if (getenv('DB_CONNECTION') !== 'pgsql') {
    $fail('La conexión debe ser PostgreSQL.');
}

$proxies = array_map(trim(...), explode(',', (string) getenv('TRUSTED_PROXIES')));
if ($proxies === [''] || in_array('*', $proxies, true)) {
    $fail('Configura TRUSTED_PROXIES con las IP o CIDR del proxy interno.');
}
