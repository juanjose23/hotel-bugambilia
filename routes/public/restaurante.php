<?php

declare(strict_types=1);

use App\Http\Controllers\Restaurante\RestauranteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Restaurante Bugambilias
|--------------------------------------------------------------------------
*/
Route::get('/restaurante', RestauranteController::class)->name('restaurante');
