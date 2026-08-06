<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Restaurante;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

final class CocinaPedidosPage extends Page
{
    public function url(): string
    {
        return '/admin/restaurante/cocina';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->waitForText('Centro de Cocina')
            ->assertSee('Centro de Cocina');
    }
}
