<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class AcercaDeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('acerca-de/AcercaDe');
    }
}
