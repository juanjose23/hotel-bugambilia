<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Restaurante;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

final class GestionMesasPage extends Page
{
    public function url(): string
    {
        return '/admin/restaurante/mesas';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->waitForText('Gestión de Mesas')
            ->assertSee('Gestión de Mesas');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@unir-mesas' => '[dusk="unir-mesas"]',
            '@mover-cuenta' => '[dusk="mover-cuenta"]',
            '@buscar-mesa' => '[dusk="buscar-mesa"]',
            '@unir-mesa-principal' => '[dusk="unir-mesa-principal"]',
            '@confirmar-union' => '[dusk="confirmar-union"]',
        ];
    }
}
