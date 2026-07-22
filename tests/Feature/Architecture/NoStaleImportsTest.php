<?php

it('has no stale App\\UseCases imports in production code', function () {
    $directories = [
        app_path('Http'),
        app_path('Filament'),
        app_path('Interactors'),
        app_path('BusinessLogic'),
        app_path('Repository'),
        app_path('Actions'),
        app_path('Services'),
        app_path('Jobs'),
        app_path('Events'),
        app_path('Listeners'),
        app_path('Notifications'),
    ];

    $staleImports = [];

    foreach ($directories as $directory) {
        if (! is_dir($directory)) {
            continue;
        }

        $files = collect(glob($directory.'/**/*.php'))->filter(fn ($file) => is_file($file));

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if (preg_match_all('/^use\s+(App\\\\UseCases\\\\.+)/m', $content, $matches)) {
                foreach ($matches[1] as $import) {
                    $staleImports[] = str_replace(app_path(), '', $file).' → '.$import;
                }
            }
        }
    }

    expect($staleImports)->toBeEmpty(
        'Found stale App\UseCases imports: '.implode(', ', $staleImports)
    );
});
