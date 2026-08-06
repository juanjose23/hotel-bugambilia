<?php

use Laravel\Dusk\Browser;

test('la pagina principal carga correctamente', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Bugambilia');
    });
});
