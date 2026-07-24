<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class ContactoController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('contacto/Contacto');
    }
}
