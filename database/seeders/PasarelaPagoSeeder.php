<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Facturacion\PasarelaPago;
use Illuminate\Database\Seeder;

class PasarelaPagoSeeder extends Seeder
{
    public function run(): void
    {
        PasarelaPago::updateOrCreate(
            ['codigo' => 'stripe'],
            [
                'nombre' => 'Stripe',
                'activa' => (bool) config('services.stripe.enabled', true),
                'modo_prueba' => config('services.stripe.mode') !== 'live',
                'configuracion' => [
                    'origen' => 'config/services.php',
                    'enabled_config_key' => 'services.stripe.enabled',
                    'public_key_config_key' => 'services.stripe.key',
                    'secret_config_key' => 'services.stripe.secret',
                    'webhook_secret_config_key' => 'services.stripe.webhook_secret',
                    'mode_config_key' => 'services.stripe.mode',
                ],
                'proveedor' => 'stripe',
                'gestion' => 'sistema',
            ]
        );

        if ($this->command !== null) {
            $this->command->info('Pasarela Stripe creada/verificada.');
        }
    }
}
