<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Restaurante;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

final class PantallaPedidosPage extends Page
{
    public function url(): string
    {
        return '/admin/restaurante/pantalla-pedidos';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->waitForText('Pantalla de Turnos & Despacho')
            ->assertSee('Pantalla de Turnos & Despacho');
    }
}
