<?php

declare(strict_types=1);

use App\BusinessLogic\Colaboradores\ValidCodigoColaborador;

test('acepta código de colaborador válido', function () {
    $rule = new ValidCodigoColaborador;
    $failed = false;

    $rule->validate('codigo', 'COL-0001', function ($message) use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('rechaza valor si no es texto', function () {
    $rule = new ValidCodigoColaborador;
    $error = null;

    $rule->validate('codigo', 1234, function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('El código debe ser una cadena de texto.');
});

test('rechaza formato de código de colaborador inválido', function () {
    $rule = new ValidCodigoColaborador;
    $error = null;

    $rule->validate('codigo', 'ABC-123', function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('El formato del código de colaborador no es válido (ej: COL-0001).');
});
