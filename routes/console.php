<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (): void {
    fwrite(STDOUT, Inspiring::quote() . PHP_EOL);
})->purpose('Display an inspiring quote');