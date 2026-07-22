<?php

declare(strict_types=1);

use App\BusinessLogic\Personas\PersonaNatural\ValidCedulaNicaragua;

test('acepta cédulas nicaragüenses válidas', function () {
    $rule = new ValidCedulaNicaragua;
    $failed = false;

    $rule->validate('cedula', '001-010102-1234G', function ($message) use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('acepta cédulas nicaragüenses válidas sin guiones', function () {
    $rule = new ValidCedulaNicaragua;
    $failed = false;

    $rule->validate('cedula', '0010101021234G', function ($message) use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('rechaza cédulas con formato inválido', function () {
    $rule = new ValidCedulaNicaragua;
    $error = null;

    $rule->validate('cedula', '123-abc', function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('El formato de la cédula no es válido. Ejemplo: 001-010102-1234G');
});

test('rechaza cédulas con municipio fuera de rango', function () {
    $rule = new ValidCedulaNicaragua;
    $error = null;

    $rule->validate('cedula', '999-010102-1234G', function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('El código de municipio de la cédula no es válido.');
});

test('rechaza cédulas con fecha de nacimiento inválida', function () {
    $rule = new ValidCedulaNicaragua;
    $error = null;

    // 31 de Febrero
    $rule->validate('cedula', '001-310202-1234G', function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('La fecha de nacimiento en la cédula no es válida.');
});

test('rechaza cédulas con letra verificadora incorrecta', function () {
    $rule = new ValidCedulaNicaragua;
    $error = null;

    // La letra correcta para 0010101021234 es G, no A
    $rule->validate('cedula', '001-010102-1234A', function ($message) use (&$error) {
        $error = $message;
    });

    expect($error)->toBe('La letra de verificación de la cédula no es correcta.');
});
