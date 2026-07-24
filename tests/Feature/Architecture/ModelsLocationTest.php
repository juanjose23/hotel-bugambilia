<?php

declare(strict_types=1);

it('keeps Eloquent models inside the repository layer', function (): void {
    $legacyModelsDirectory = app_path('Models');

    expect(is_dir($legacyModelsDirectory))
        ->toBeFalse('Eloquent models must live in app/Repository/Models, not app/Models.');
});
