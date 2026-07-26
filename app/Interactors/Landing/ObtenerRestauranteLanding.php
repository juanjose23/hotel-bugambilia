<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class ObtenerRestauranteLanding
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @return array{
     *     restaurante: array<string, mixed>|null,
     *     ambientes: array<int, array<string, mixed>>,
     *     mesas: array<int, array<string, mixed>>,
     *     menu: array<int, array<string, mixed>>
     * }
     */
    public function ejecutar(): array
    {
        $restaurante = $this->repositorio->obtenerRestauranteParaLanding();

        if (! $restaurante instanceof Espacio) {
            return [
                'restaurante' => null,
                'ambientes' => [],
                'mesas' => [],
                'menu' => [],
            ];
        }

        $metaRestaurante = $this->decodificarMeta($restaurante->meta_datos);

        $imagenesRestaurante = $restaurante->imagenes->pluck('url')->filter()->values()->toArray();
        if (empty($imagenesRestaurante)) {
            $imagenesRestaurante = [
                '/images/terrace.jpg',
                '/images/service-kitchen.png',
                '/images/service-bartender.png',
            ];
        }

        $mesasMapped = $this->repositorio->obtenerMesasDeRestaurante($restaurante->id)
            ->map(function (Espacio $mesa): array {
                $metaMesa = $this->decodificarMeta($mesa->meta_datos);

                return [
                    'id' => $mesa->id,
                    'nombre' => $mesa->nombre ?? '',
                    'capacidad' => $mesa->capacidad_personas ?? 2,
                    'tipo_mesa' => $metaMesa['tipo_mesa'] ?? 'cuadrada',
                    'zona' => $metaMesa['zona_restaurante'] ?? 'interior',
                ];
            });

        /** @var array<int, array{id: int, nombre: string, capacidad: int, tipo_mesa: string, zona: string}> $mesas */
        $mesas = $mesasMapped->toArray();

        $ambientesBD = $this->repositorio->obtenerAmbientesDeRestaurante($restaurante->id);

        $fallbackFotosAmbientes = [
            'interior' => ['/images/service-kitchen.png', '/images/terrace.jpg'],
            'terraza' => ['/images/terrace.jpg', '/images/service-kitchen.png'],
            'barra' => ['/images/service-bartender.png', '/images/terrace.jpg'],
            'vip' => ['/images/terrace.jpg', '/images/service-bartender.png'],
        ];

        $ambientes = $ambientesBD->map(function (Espacio $amb) use ($mesas, $fallbackFotosAmbientes): array {
            $meta = $this->decodificarMeta($amb->meta_datos);
            $zonaRaw = $meta['zona_restaurante'] ?? 'interior';
            $zona = is_string($zonaRaw) ? $zonaRaw : 'interior';
            $caracteristicas = is_array($meta['caracteristicas'] ?? null)
                ? $meta['caracteristicas']
                : ['Musica de Fondo', 'Iluminacion Calida', 'Atencion Personalizada'];

            $imagenes = $amb->imagenes->pluck('url')->filter()->values()->toArray();
            if (empty($imagenes)) {
                $imagenes = $fallbackFotosAmbientes[$zona] ?? ['/images/terrace.jpg'];
            }

            $mesasDeZona = array_filter($mesas, fn (array $m): bool => $m['zona'] === $zona);

            return [
                'id' => $amb->id,
                'codigo' => $amb->codigo,
                'nombre' => $amb->nombre,
                'tipo' => $amb->tipo->value,
                'capacidad' => $amb->capacidad_personas ?? 20,
                'descripcion' => $amb->descripcion ?? 'Ambiente disenado para ofrecer una velada gastronomica inigualable.',
                'zona' => $zona,
                'caracteristicas' => $caracteristicas,
                'imagenes' => $imagenes,
                'mesas_count' => count($mesasDeZona),
                'mesas' => array_values($mesasDeZona),
            ];
        })->values()->all();

        /** @var array<int, array<string, mixed>> $ambientes */
        $ambientes = $ambientes;

        if (empty($ambientes)) {
            $ambientes = [
                [
                    'id' => 1,
                    'codigo' => 'AMB-SALON',
                    'nombre' => 'Salon Principal Bugambilias',
                    'tipo' => 'ambiente',
                    'capacidad' => 25,
                    'descripcion' => 'Salon climatizado de ambiente elegante y sofisticado, perfecto para cenas formales y reuniones familiares.',
                    'zona' => 'interior',
                    'caracteristicas' => ['Aire Acondicionado', 'Musica de Fondo', 'Iluminacion Calida', 'Vista a la Galeria'],
                    'imagenes' => ['/images/service-kitchen.png', '/images/terrace.jpg'],
                    'mesas_count' => count(array_filter($mesas, fn ($m): bool => $m['zona'] === 'interior')),
                    'mesas' => array_values(array_filter($mesas, fn ($m): bool => $m['zona'] === 'interior')),
                ],
                [
                    'id' => 2,
                    'codigo' => 'AMB-TERRAZA',
                    'nombre' => 'Terraza al Aire Libre',
                    'tipo' => 'terraza',
                    'capacidad' => 20,
                    'descripcion' => 'Rodeada de exuberantes jardines tropicales y flores de bugambilia, disfrutando la brisa fresca de Esteli.',
                    'zona' => 'terraza',
                    'caracteristicas' => ['Vista al Jardin', 'Pergola Iluminada', 'Brisa Natural', 'Mesas al Aire Libre'],
                    'imagenes' => ['/images/terrace.jpg', '/images/service-kitchen.png'],
                    'mesas_count' => count(array_filter($mesas, fn ($m): bool => $m['zona'] === 'terraza')),
                    'mesas' => array_values(array_filter($mesas, fn ($m): bool => $m['zona'] === 'terraza')),
                ],
                [
                    'id' => 3,
                    'codigo' => 'AMB-BAR',
                    'nombre' => 'Bar & Lounge El Mirador',
                    'tipo' => 'bar',
                    'capacidad' => 15,
                    'descripcion' => 'Barra moderna especializada en cocteleria de autor, seleccion de vinos, cervezas artesanales y tapas.',
                    'zona' => 'barra',
                    'caracteristicas' => ['Barra de Cocteles', 'Pantalla HD', 'Musica Lounge', 'Seleccion de Vinos'],
                    'imagenes' => ['/images/service-bartender.png', '/images/terrace.jpg'],
                    'mesas_count' => count(array_filter($mesas, fn ($m): bool => $m['zona'] === 'barra')),
                    'mesas' => array_values(array_filter($mesas, fn ($m): bool => $m['zona'] === 'barra')),
                ],
                [
                    'id' => 4,
                    'codigo' => 'AMB-VIP',
                    'nombre' => 'Cenador Privado VIP',
                    'tipo' => 'ambiente',
                    'capacidad' => 10,
                    'descripcion' => 'Espacio reservado con atencion personalizada de garzon, ideal para celebraciones privadas y aniversarios.',
                    'zona' => 'vip',
                    'caracteristicas' => ['Servicio Exclusivo', 'Garzon Dedicado', 'Ambiente Privado', 'Decoracion Especial'],
                    'imagenes' => ['/images/terrace.jpg', '/images/service-bartender.png'],
                    'mesas_count' => count(array_filter($mesas, fn ($m): bool => $m['zona'] === 'vip')),
                    'mesas' => array_values(array_filter($mesas, fn ($m): bool => $m['zona'] === 'vip')),
                ],
            ];
        }

        $menu = $this->repositorio->obtenerMenuParaLanding()
            ->map(function (Plato $p): array {
                $precio = $p->precios->first();
                $img = $p->imagenes->first();
                $catNombre = $p->categoria ? $p->categoria->nombre : 'Especialidades';
                $catCodigo = $p->categoria ? $p->categoria->codigo : 'RESTAURANTE';

                /** @var array<int, string> $etiquetas */
                $etiquetas = [];
                $nombreLower = mb_strtolower($p->nombre);
                if (str_contains($nombreLower, 'lomo') || str_contains($nombreLower, 'filete') || str_contains($nombreLower, 'camarones')) {
                    $etiquetas[] = 'Especialidad del Chef';
                }
                if (str_contains($nombreLower, 'tres leches') || str_contains($nombreLower, 'nachos')) {
                    $etiquetas[] = 'Favorito de la Casa';
                }
                if (str_contains($nombreLower, 'pescado') || str_contains($nombreLower, 'ceviche')) {
                    $etiquetas[] = 'Ingredientes Frescos';
                }

                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'descripcion' => $p->descripcion ?? '',
                    'categoria' => $catNombre,
                    'categoria_codigo' => $catCodigo,
                    'precio' => $precio ? (float) (string) $precio->precio : null,
                    'moneda' => $precio?->moneda->simbolo ?? 'C$',
                    'imagen' => $img ? $img->url : $this->obtenerImagenMenuFallback($catNombre),
                    'etiquetas' => $etiquetas,
                    'tiempo_preparacion' => $p->tiempo_preparacion ?? '15 - 25 min',
                    'disponible' => true,
                ];
            })->values()->all();

        /** @var array<int, array<string, mixed>> $menu */
        $menu = $menu;

        return [
            'restaurante' => [
                'id' => $restaurante->id,
                'nombre' => $restaurante->nombre ?? 'Restaurante Bugambilias',
                'descripcion' => $restaurante->descripcion ?? 'Experiencia gastronomica excepcional en Esteli. Sabores locales e internacionales en ambientes acogedores.',
                'capacidad' => $restaurante->capacidad_personas ?? 40,
                'imagenes' => $imagenesRestaurante,
                'tipo_cocina' => $metaRestaurante['tipo_cocina'] ?? 'Nicaraguense e Internacional',
                'tipo_servicio' => $metaRestaurante['tipo_servicio'] ?? 'A la carta / Gourmet',
                'horario_desayuno' => $metaRestaurante['horario_desayuno'] ?? '07:00 - 10:30 AM',
                'horario_almuerzo' => $metaRestaurante['horario_almuerzo'] ?? '12:00 - 03:30 PM',
                'horario_cena' => $metaRestaurante['horario_cena'] ?? '06:00 - 10:00 PM',
            ],
            'ambientes' => $ambientes,
            'mesas' => $mesas,
            'menu' => $menu,
        ];
    }

    private function obtenerImagenMenuFallback(string $categoria): string
    {
        $cat = mb_strtolower($categoria);
        if (str_contains($cat, 'entrada')) {
            return '/images/service-kitchen.png';
        }
        if (str_contains($cat, 'postre')) {
            return '/images/terrace.jpg';
        }
        if (str_contains($cat, 'bebida') || str_contains($cat, 'coctel')) {
            return '/images/service-bartender.png';
        }

        return '/images/service-kitchen.png';
    }

    /**
     * @return array<string, mixed>
     */
    private function decodificarMeta(mixed $datos): array
    {
        if (is_string($datos)) {
            $decoded = json_decode($datos, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($datos) ? $datos : [];
    }
}
