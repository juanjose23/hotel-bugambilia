<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class ObtenerRestauranteLanding
{
    /** @var array<string, list<string>> */
    private const array FALLBACK_FOTOS_AMBIENTES = [
        'interior' => ['/images/service-kitchen.png', '/images/terrace.jpg'],
        'terraza' => ['/images/terrace.jpg', '/images/service-kitchen.png'],
        'barra' => ['/images/service-bartender.png', '/images/terrace.jpg'],
        'vip' => ['/images/terrace.jpg', '/images/service-bartender.png'],
    ];

    /** @var list<string> */
    private const array IMAGENES_RESTAURANTE_FALLBACK = [
        '/images/terrace.jpg',
        '/images/service-kitchen.png',
        '/images/service-bartender.png',
    ];

    /** @var list<string> */
    private const array KEYWORDS_CHEF = ['lomo', 'filete', 'camarones'];

    /** @var list<string> */
    private const array KEYWORDS_FAVORITO = ['tres leches', 'nachos'];

    /** @var list<string> */
    private const array KEYWORDS_FRESCO = ['pescado', 'ceviche'];

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
            return ['restaurante' => null, 'ambientes' => [], 'mesas' => [], 'menu' => []];
        }

        $mesas = $this->mapearMesas($restaurante->id);
        $ambientes = $this->mapearAmbientes($restaurante->id, $mesas);

        return [
            'restaurante' => $this->mapearRestaurante($restaurante),
            'ambientes' => $ambientes,
            'mesas' => $mesas,
            'menu' => $this->mapearMenu(),
        ];
    }

    // -------------------------------------------------------------------------
    // Restaurante
    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function mapearRestaurante(Espacio $restaurante): array
    {
        $meta = $this->decodificarMeta($restaurante->meta_datos);
        $imagenes = $restaurante->imagenes->pluck('url')->filter()->values()->toArray();

        return [
            'id' => $restaurante->id,
            'nombre' => $restaurante->nombre ?? 'Restaurante Bugambilias',
            'descripcion' => $restaurante->descripcion ?? 'Experiencia gastronomica excepcional en Esteli. Sabores locales e internacionales en ambientes acogedores.',
            'capacidad' => $restaurante->capacidad_personas ?? 40,
            'imagenes' => $imagenes ?: self::IMAGENES_RESTAURANTE_FALLBACK,
            'tipo_cocina' => $meta['tipo_cocina'] ?? 'Nicaraguense e Internacional',
            'tipo_servicio' => $meta['tipo_servicio'] ?? 'A la carta / Gourmet',
            'horario_desayuno' => $meta['horario_desayuno'] ?? '07:00 - 10:30 AM',
            'horario_almuerzo' => $meta['horario_almuerzo'] ?? '12:00 - 03:30 PM',
            'horario_cena' => $meta['horario_cena'] ?? '06:00 - 10:00 PM',
        ];
    }

    // -------------------------------------------------------------------------
    // Mesas
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearMesas(int $restauranteId): array
    {
        return $this->repositorio
            ->obtenerMesasDeRestaurante($restauranteId)
            ->map(fn (Espacio $mesa): array => $this->mesaToArray($mesa))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mesaToArray(Espacio $mesa): array
    {
        $meta = $this->decodificarMeta($mesa->meta_datos);

        return [
            'id' => $mesa->id,
            'nombre' => $mesa->nombre ?? '',
            'capacidad' => $mesa->capacidad_personas ?? 2,
            'tipo_mesa' => $meta['tipo_mesa'] ?? 'cuadrada',
            'zona' => $meta['zona_restaurante'] ?? 'interior',
        ];
    }

    // -------------------------------------------------------------------------
    // Ambientes
    // -------------------------------------------------------------------------

    /**
     * @param  array<int, array<string, mixed>>  $mesas
     * @return array<int, array<string, mixed>>
     */
    private function mapearAmbientes(int $restauranteId, array $mesas): array
    {
        $ambientesBD = $this->repositorio->obtenerAmbientesDeRestaurante($restauranteId);

        $ambientes = $ambientesBD
            ->map(fn (Espacio $amb): array => $this->ambienteToArray($amb, $mesas))
            ->values()
            ->all();

        return $ambientes ?: $this->ambientesFallback($mesas);
    }

    /**
     * @param  array<int, array<string, mixed>>  $mesas
     * @return array<string, mixed>
     */
    private function ambienteToArray(Espacio $amb, array $mesas): array
    {
        $meta = $this->decodificarMeta($amb->meta_datos);
        $zona = is_string($meta['zona_restaurante'] ?? null) ? $meta['zona_restaurante'] : 'interior';
        $imagenes = $amb->imagenes->pluck('url')->filter()->values()->toArray();

        /** @var list<string> $caracteristicas */
        $caracteristicas = is_array($meta['caracteristicas'] ?? null)
            ? $meta['caracteristicas']
            : ['Musica de Fondo', 'Iluminacion Calida', 'Atencion Personalizada'];

        $mesasDeZona = array_values(array_filter($mesas, fn (array $m): bool => $m['zona'] === $zona));

        return [
            'id' => $amb->id,
            'codigo' => $amb->codigo,
            'nombre' => $amb->nombre,
            'tipo' => $amb->tipo->value,
            'capacidad' => $amb->capacidad_personas ?? 20,
            'descripcion' => $amb->descripcion ?? 'Ambiente disenado para ofrecer una velada gastronomica inigualable.',
            'zona' => $zona,
            'caracteristicas' => $caracteristicas,
            'imagenes' => $imagenes ?: (self::FALLBACK_FOTOS_AMBIENTES[$zona] ?? ['/images/terrace.jpg']),
            'mesas_count' => count($mesasDeZona),
            'mesas' => $mesasDeZona,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $mesas
     * @return list<array<string, mixed>>
     */
    private function ambientesFallback(array $mesas): array
    {
        return [
            $this->ambienteFallbackEntry('AMB-SALON', 'Salon Principal Bugambilias', 'ambiente', 25, 'interior',
                'Salon climatizado de ambiente elegante y sofisticado, perfecto para cenas formales y reuniones familiares.',
                ['Aire Acondicionado', 'Musica de Fondo', 'Iluminacion Calida', 'Vista a la Galeria'],
                ['/images/service-kitchen.png', '/images/terrace.jpg'], $mesas),
            $this->ambienteFallbackEntry('AMB-TERRAZA', 'Terraza al Aire Libre', 'terraza', 20, 'terraza',
                'Rodeada de exuberantes jardines tropicales y flores de bugambilia, disfrutando la brisa fresca de Esteli.',
                ['Vista al Jardin', 'Pergola Iluminada', 'Brisa Natural', 'Mesas al Aire Libre'],
                ['/images/terrace.jpg', '/images/service-kitchen.png'], $mesas),
            $this->ambienteFallbackEntry('AMB-BAR', 'Bar & Lounge El Mirador', 'bar', 15, 'barra',
                'Barra moderna especializada en cocteleria de autor, seleccion de vinos, cervezas artesanales y tapas.',
                ['Barra de Cocteles', 'Pantalla HD', 'Musica Lounge', 'Seleccion de Vinos'],
                ['/images/service-bartender.png', '/images/terrace.jpg'], $mesas),
            $this->ambienteFallbackEntry('AMB-VIP', 'Cenador Privado VIP', 'ambiente', 10, 'vip',
                'Espacio reservado con atencion personalizada de garzon, ideal para celebraciones privadas y aniversarios.',
                ['Servicio Exclusivo', 'Garzon Dedicado', 'Ambiente Privado', 'Decoracion Especial'],
                ['/images/terrace.jpg', '/images/service-bartender.png'], $mesas),
        ];
    }

    /**
     * @param  list<string>  $caracteristicas
     * @param  list<string>  $imagenes
     * @param  array<int, array<string, mixed>>  $todasLasMesas
     * @return array<string, mixed>
     */
    private function ambienteFallbackEntry(
        string $codigo,
        string $nombre,
        string $tipo,
        int $capacidad,
        string $zona,
        string $descripcion,
        array $caracteristicas,
        array $imagenes,
        array $todasLasMesas,
    ): array {
        $mesasDeZona = array_values(array_filter($todasLasMesas, fn (array $m): bool => $m['zona'] === $zona));

        return [
            'id' => 0,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'capacidad' => $capacidad,
            'descripcion' => $descripcion,
            'zona' => $zona,
            'caracteristicas' => $caracteristicas,
            'imagenes' => $imagenes,
            'mesas_count' => count($mesasDeZona),
            'mesas' => $mesasDeZona,
        ];
    }

    // -------------------------------------------------------------------------
    // Menú
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearMenu(): array
    {
        return $this->repositorio
            ->obtenerMenuParaLanding()
            ->map(fn (Plato $p): array => $this->platoToArray($p))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function platoToArray(Plato $p): array
    {
        $precio = $p->precios->first();
        $img = $p->imagenes->first();
        $catNombre = $p->categoria->nombre ?? 'Especialidades';
        $catCodigo = $p->categoria->codigo ?? 'RESTAURANTE';

        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'descripcion' => $p->descripcion ?? '',
            'categoria' => $catNombre,
            'categoria_codigo' => $catCodigo,
            'precio' => $precio ? (float) (string) $precio->precio : null,
            'moneda' => $precio?->moneda->simbolo ?? 'C$',
            'imagen' => $img->url ?? $this->obtenerImagenMenuFallback($catNombre),
            'etiquetas' => $this->resolverEtiquetasPlato($p->nombre),
            'tiempo_preparacion' => $p->tiempo_preparacion ?? '15 - 25 min',
            'disponible' => true,
        ];
    }

    /**
     * @return list<string>
     */
    private function resolverEtiquetasPlato(string $nombre): array
    {
        $nombreLower = mb_strtolower($nombre);
        $etiquetas = [];

        foreach (self::KEYWORDS_CHEF as $keyword) {
            if (str_contains($nombreLower, $keyword)) {
                $etiquetas[] = 'Especialidad del Chef';
                break;
            }
        }

        foreach (self::KEYWORDS_FAVORITO as $keyword) {
            if (str_contains($nombreLower, $keyword)) {
                $etiquetas[] = 'Favorito de la Casa';
                break;
            }
        }

        foreach (self::KEYWORDS_FRESCO as $keyword) {
            if (str_contains($nombreLower, $keyword)) {
                $etiquetas[] = 'Ingredientes Frescos';
                break;
            }
        }

        return $etiquetas;
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

    // -------------------------------------------------------------------------
    // Utilidades
    // -------------------------------------------------------------------------

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
