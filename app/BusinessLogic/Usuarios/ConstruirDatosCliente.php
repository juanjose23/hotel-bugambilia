<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Repository\Queries\Catalogos\ObtenerCatalogoClienteRegularQuery;

final readonly class ConstruirDatosCliente
{
    public function __construct(
        private ObtenerCatalogoClienteRegularQuery $catalogoRegular,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function construir(array $datos, string $tipoPersona): array
    {
        $catalogo = $this->catalogoRegular->obtener();

        $cliente = [
            'tipo_persona' => $tipoPersona,
            'catalogo_id' => $catalogo->id ?? 1,
            'primer_nombre' => $datos['primer_nombre'] ?? $datos['razon_social'] ?? '',
            'email' => $datos['email'],
            'telefono' => $datos['phone'] ?? '',
            'password' => $datos['password'],
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
        ];

        if ($tipoPersona === 'juridica') {
            $cliente['razon_social'] = $datos['razon_social'] ?? '';
        } else {
            $cliente['primer_apellido'] = $datos['primer_apellido'] ?? '';
        }

        return $cliente;
    }
}
