<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class FavoritosController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('favoritos/Favoritos');
    }
}
