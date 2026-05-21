<?php

namespace App\UseCases\Compras\Solicitudes\Mutations;

use App\Models\Catalogos\Catalogo;
use App\Models\Compras\Solicitud;

class GenerarCodigoSolicitud
{
    public function ejecutar(int $departamentoId): string
    {
        $departamento = Catalogo::findOrFail($departamentoId);
        $siglas = $this->obtenerSiglas($departamento->codigo);

        $ultimo = Solicitud::withTrashed()
            ->where('codigo', 'like', "S-{$siglas}-%")
            ->orderByDesc('codigo')
            ->first();

        $numero = $ultimo
            ? intval(substr($ultimo->codigo, strlen("S-{$siglas}-"))) + 1
            : 1;

        return "S-{$siglas}-".str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }

    private function obtenerSiglas(string $codigoCatalogo): string
    {
        $partes = explode('_', $codigoCatalogo);

        if (count($partes) === 1) {
            return strtoupper(substr($codigoCatalogo, 0, 4));
        }

        $sinPrefijo = array_slice($partes, 1);
        $siglas = '';

        foreach ($sinPrefijo as $palabra) {
            $siglas .= substr($palabra, 0, 1);
        }

        return strtoupper($siglas);
    }
}
