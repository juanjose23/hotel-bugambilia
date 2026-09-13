<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Actions\Facturacion\AsegurarPasarelaDesdeConfig;
use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\Actions\Restaurante\ConstruirUrlWhatsapp;
use App\BusinessLogic\Restaurante\Delivery\ObtenerDepartamentosDelivery;
use App\BusinessLogic\Restaurante\Delivery\ValidarDepartamentoDelivery;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Facturacion\RegistrarTransaccionPasarela;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Interactors\Restaurante\Pedidos\RecalcularTotalesPedido;
use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Restaurante\Landing\ObtenerPlatosPorIdsQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CrearPedidoPublicoLanding
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private RegistrarClienteRapido $registrarClienteRapido,
        private RecalcularTotalesPedido $recalcularTotales,
        private EnviarPedidoACocina $enviarACocina,
        private StripePaymentIntentClient $stripeClient,
        private StripeMontoMenorUnidad $montoMenorUnidad,
        private AsegurarPasarelaDesdeConfig $asegurarPasarela,
        private RegistrarTransaccionPasarela $registrarTransaccion,
        private ObtenerMonedaPredeterminadaQuery $obtenerMoneda,
        private ObtenerPlatosPorIdsQuery $obtenerPlatos,
        private ConstruirUrlWhatsapp $construirUrlWhatsapp,
        private ValidarDepartamentoDelivery $validarDepartamentoDelivery = new ValidarDepartamentoDelivery(
            new ObtenerDepartamentosDelivery
        ),
    ) {}

    /**
     * @param array{
     *     nombre: string,
     *     email?: string|null,
     *     telefono: string,
     *     departamento?: string|null,
     *     municipio?: string|null,
     *     direccion: string,
     *     metodo_pago: string,
     *     monto_paga_con?: string|null,
     *     notas?: string|null,
     *     items: array<int, array{plato_id: int, cantidad: int, observaciones?: string|null}>
     * } $datos
     * @return array{
     *     success: bool,
     *     pedido_id: int,
     *     codigo: string,
     *     subtotal: float,
     *     costo_delivery: float,
     *     total: float,
     *     moneda: string,
     *     metodo_pago: string,
     *     whatsapp_url: string,
     *     stripe_pago: ?array{client_secret: string, publishable_key: string, monto: float},
     *     mensaje: string
     * }
     */
    public function ejecutar(array $datos): array
    {
        if (empty($datos['items'])) {
            throw new DomainException('El pedido debe contener al menos un platillo.');
        }

        return DB::transaction(function () use ($datos): array {
            // 1. Validar departamento y municipio de entrega
            $departamentoNom = ! empty($datos['departamento']) ? (string) $datos['departamento'] : 'Estelí';
            $municipioNom = ! empty($datos['municipio']) ? (string) $datos['municipio'] : 'Estelí';
            $departamentoInfo = $this->validarDepartamentoDelivery->ejecutar($departamentoNom, $municipioNom);

            // 2. Resolver o registrar el cliente
            $cliente = $this->resolverCliente($datos['nombre'], $datos['telefono']);

            // 3. Generar código único para el pedido
            $codigo = 'PED-WEB-'.date('ymd').'-'.strtoupper(Str::random(5));

            // 4. Obtener platillos válidos y calcular subtotales reales desde la BD
            $platoIds = array_map(static fn (mixed $id): int => (int) $id, array_column($datos['items'], 'plato_id'));
            $platos = $this->obtenerPlatos->ejecutar($platoIds);

            $itemsParaInsertar = [];
            $subtotalCalculado = 0.0;
            $monedaSimbolo = 'C$';

            foreach ($datos['items'] as $itemData) {
                $platoId = (int) $itemData['plato_id'];
                $plato = $platos->get($platoId);

                if (! $plato instanceof Plato) {
                    continue;
                }

                $cantidad = max(1, (int) $itemData['cantidad']);
                $precioObj = $plato->precios->first();
                $precioUnitario = $precioObj !== null ? (float) $precioObj->precio : 0.0;
                $monedaSimbolo = $precioObj->moneda->simbolo ?? $monedaSimbolo;
                $itemSubtotal = round($precioUnitario * $cantidad, 2);
                $subtotalCalculado += $itemSubtotal;

                $itemsParaInsertar[] = [
                    'plato' => $plato,
                    'plato_id' => $plato->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $itemSubtotal,
                    'observaciones' => ! empty($itemData['observaciones']) ? (string) $itemData['observaciones'] : null,
                ];
            }

            if ($itemsParaInsertar === []) {
                throw new DomainException('No se encontraron platillos válidos para procesar el pedido.');
            }

            $costoDelivery = (float) $departamentoInfo['costo_envio'];
            $totalCalculado = round($subtotalCalculado + $costoDelivery, 2);

            // 5. Construir notas operativas con datos de entrega y pago
            $metodoPagoTexto = match ($datos['metodo_pago']) {
                PasarelaCodigo::Stripe->value => 'Tarjeta de Crédito / Débito (Stripe)',
                default => 'Efectivo'.(! empty($datos['monto_paga_con']) ? " (Paga con: {$datos['monto_paga_con']})" : ''),
            };

            $direccionCompleta = "{$departamentoInfo['nombre']}, {$municipioNom} - {$datos['direccion']}";

            $notasPedido = implode(' | ', array_filter([
                '🛵 PEDIDO A DOMICILIO (WEB)',
                "Cliente: {$datos['nombre']}",
                "Tel: {$datos['telefono']}",
                ! empty($datos['email']) ? "Email: {$datos['email']}" : null,
                "Zona: {$departamentoInfo['nombre']} / {$municipioNom}",
                "Dirección: {$datos['direccion']}",
                "Pago: {$metodoPagoTexto}",
                ! empty($datos['notas']) ? "Notas: {$datos['notas']}" : null,
            ]));

            // 6. Crear el Pedido en la base de datos
            $pedido = new Pedido([
                'codigo' => $codigo,
                'cliente_id' => $cliente->id,
                'mesa_id' => null, // Es delivery, sin mesa física asignada
                'estado' => EstadoPedido::ABIERTO,
                'subtotal' => $subtotalCalculado,
                'consecutivo_comanda' => 1,
                'abierto_en' => now(),
                'notas' => $notasPedido,
            ]);

            $this->repositorio->guardarPedido($pedido);

            // 7. Crear los items del pedido
            foreach ($itemsParaInsertar as $item) {
                $this->repositorio->crearPedidoItem([
                    'pedido_id' => $pedido->id,
                    'plato_id' => $item['plato_id'],
                    'area_cocina' => AreaCocina::COCINA,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'observaciones' => $item['observaciones'],
                    'estado' => EstadoItemPedido::PENDIENTE,
                ]);
            }

            // 8. Recalcular y enviar comanda a cocina
            $this->recalcularTotales->ejecutar($pedido);
            $this->enviarACocina->ejecutar($pedido);

            // 9. Construir URL de WhatsApp con comanda oficial
            $whatsappUrl = $this->construirUrlWhatsapp(
                codigo: $codigo,
                nombre: $datos['nombre'],
                telefono: $datos['telefono'],
                direccion: $direccionCompleta,
                metodoPagoTexto: $metodoPagoTexto,
                items: $itemsParaInsertar,
                subtotal: $subtotalCalculado,
                costoDelivery: $costoDelivery,
                total: $totalCalculado,
                moneda: $monedaSimbolo,
                notas: $datos['notas'] ?? null,
            );

            // 10. Procesamiento con Stripe si aplica
            $stripePago = null;
            if ($datos['metodo_pago'] === PasarelaCodigo::Stripe->value) {
                $stripePago = $this->generarIntentoStripe($pedido, $totalCalculado, $datos);
            }

            return [
                'success' => true,
                'pedido_id' => (int) $pedido->id,
                'codigo' => $codigo,
                'subtotal' => $subtotalCalculado,
                'costo_delivery' => $costoDelivery,
                'total' => $totalCalculado,
                'moneda' => $monedaSimbolo,
                'metodo_pago' => $datos['metodo_pago'],
                'whatsapp_url' => $whatsappUrl,
                'stripe_pago' => $stripePago,
                'mensaje' => "¡Pedido {$codigo} registrado exitosamente!",
            ];
        });
    }

    private function resolverCliente(string $nombre, string $telefono): Cliente
    {
        $usuario = Auth::user();
        $clienteAuth = $usuario?->persona?->cliente;

        if ($clienteAuth instanceof Cliente) {
            return $clienteAuth;
        }

        $partes = explode(' ', trim($nombre), 2);
        $primerNombre = $partes[0];
        $primerApellido = $partes[1] ?? '';

        return $this->registrarClienteRapido->ejecutar([
            'primer_nombre' => $primerNombre,
            'primer_apellido' => $primerApellido,
            'telefono' => $telefono,
            'tipo_persona' => 'natural',
        ]);
    }

    /**
     * @param  array{nombre: string, email?: string|null, telefono: string}  $datos
     * @return array{client_secret: string, publishable_key: string, monto: float}
     */
    private function generarIntentoStripe(Pedido $pedido, float $total, array $datos): array
    {
        $moneda = $this->obtenerMoneda->ejecutar();
        if (! $moneda instanceof Moneda) {
            throw new DomainException('No hay una moneda predeterminada configurada para pagos con Stripe.');
        }

        $monedaCodigo = (string) ($moneda->codigo ?? 'USD');
        $pasarela = $this->asegurarPasarela->ejecutar(PasarelaCodigo::Stripe);
        $idempotencyKey = 'stripe-pedido-'.$pedido->id.'-'.$total.'-'.$monedaCodigo;

        $customerId = null;
        $usuario = Auth::user();
        $emailCliente = ! empty($datos['email'])
            ? trim($datos['email'])
            : ($usuario instanceof User ? $usuario->email : null);

        if ($emailCliente !== null && $emailCliente !== '') {
            try {
                $stripeCustomer = $this->stripeClient->crearOBuscarCliente(
                    email: $emailCliente,
                    nombre: $datos['nombre'],
                    telefono: $datos['telefono'],
                    metadata: [
                        'pedido_id' => (string) $pedido->id,
                        'codigo_pedido' => (string) $pedido->codigo,
                    ],
                );
                $customerId = is_string($stripeCustomer['id'] ?? null) ? $stripeCustomer['id'] : null;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $intent = $this->stripeClient->crearPaymentIntent(
            montoMenorUnidad: $this->montoMenorUnidad->ejecutar($total, $monedaCodigo),
            moneda: $monedaCodigo,
            idempotencyKey: $idempotencyKey,
            metadata: [
                'pedido_id' => (string) $pedido->id,
                'codigo_pedido' => (string) $pedido->codigo,
                'cliente' => $datos['nombre'],
                'telefono' => $datos['telefono'],
                'email' => $emailCliente ?? '',
            ],
            receiptEmail: $emailCliente,
            customerId: $customerId,
            description: "Pago de comanda #{$pedido->codigo} en Restaurante Hotel Bugambilias",
        );

        $paymentIntentId = is_string($intent['id'] ?? null) ? (string) $intent['id'] : '';
        $clientSecret = is_string($intent['client_secret'] ?? null) ? (string) $intent['client_secret'] : '';
        $publishableKey = is_string(config('services.stripe.key')) ? (string) config('services.stripe.key') : '';

        $this->registrarTransaccion->ejecutar(
            pasarela: $pasarela,
            monto: $total,
            moneda: $moneda,
            idempotencyKey: $idempotencyKey,
            estado: EstadoTransaccionPago::Pendiente,
            referenciaPasarela: $paymentIntentId,
            requestPayload: [
                'pedido_id' => $pedido->id,
                'monto' => $total,
                'moneda' => $monedaCodigo,
            ],
            responsePayload: $intent,
        );

        return [
            'client_secret' => $clientSecret,
            'publishable_key' => $publishableKey,
            'monto' => $total,
        ];
    }

    /**
     * @param  array<int, array{plato: Plato, cantidad: int, precio_unitario: float, subtotal: float}>  $items
     */
    private function construirUrlWhatsapp(
        string $codigo,
        string $nombre,
        string $telefono,
        string $direccion,
        string $metodoPagoTexto,
        array $items,
        float $subtotal,
        float $costoDelivery,
        float $total,
        string $moneda,
        ?string $notas,
    ): string {
        $platosLineas = [];
        foreach ($items as $item) {
            $platosLineas[] = "• {$item['cantidad']}x {$item['plato']->nombre} - {$moneda} ".number_format($item['subtotal'], 2);
        }

        $lineas = [
            '*¡Nuevo Pedido a Domicilio — Hotel Bugambilias!* 🛵',
            "🔖 *Comanda Nº:* {$codigo}",
            '',
            "👤 *Cliente:* {$nombre}",
            "📞 *Teléfono:* {$telefono}",
            "📍 *Dirección de Entrega:* {$direccion}",
            "💳 *Método de Pago:* {$metodoPagoTexto}",
            '',
            '*🍽️ Platillos:*',
            implode("\n", $platosLineas),
            '',
            '━━━━━━━━━━━━━━━━━━',
            "💵 *Subtotal:* {$moneda} ".number_format($subtotal, 2),
            "🛵 *Envío / Delivery:* {$moneda} ".number_format($costoDelivery, 2),
            "💰 *TOTAL:* {$moneda} ".number_format($total, 2),
            '━━━━━━━━━━━━━━━━━━',
        ];

        if (! empty($notas)) {
            $lineas[] = "📝 *Notas:* {$notas}";
        }

        $lineas[] = '';
        $lineas[] = '¿Me confirman la recepción del pedido y el tiempo estimado de entrega, por favor?';

        return $this->construirUrlWhatsapp->ejecutar($lineas);
    }
}
