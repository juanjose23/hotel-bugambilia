<?php

declare(strict_types=1);

namespace App\Repository\Queries\Colaboradores;

use App\Enums\Personas\TipoSangre;
use App\Repository\Models\Personas\Persona;
use App\Support\HotelInfo;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ObtenerDatosCarnet
{
    public function obtenerNombreCompleto(Persona $persona): string
    {
        return trim(
            ($persona->primer_nombre ?? '').' '.
            ($persona->segundo_nombre ?? '').' '.
            ($persona->personaNatural->primer_apellido ?? '').' '.
            ($persona->personaNatural->segundo_apellido ?? '')
        );
    }

    public function obtenerCodigo(Persona $persona): string
    {
        return $persona->colaborador->codigo ?? 'SIN-CODIGO';
    }

    public function obtenerUrlFoto(Persona $persona): string
    {
        return $persona->colaborador?->imagen?->url
            ? asset('storage/'.$persona->colaborador->imagen->url)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->obtenerNombreCompleto($persona)).'&size=512&background=711c37&color=fff';
    }

    public function obtenerCargoActual(Persona $persona): string
    {
        return $persona->colaborador?->cargoActual()?->cargo->nombre ?? 'Sin cargo asignado';
    }

    public function obtenerDepartamentoActual(Persona $persona): string
    {
        return $persona->colaborador?->cargoActual()?->departamento->nombre ?? 'Sin departamento';
    }

    public function obtenerTipoSangre(Persona $persona): string
    {
        $bloodType = $persona->colaborador?->datosMedicos?->tipo_sangre;

        if (! $bloodType) {
            return 'No definido';
        }

        return TipoSangre::options()[$bloodType] ?? $bloodType;
    }

    public function obtenerDireccion(Persona $persona): string
    {
        return $persona->direccion ?: 'Sin dirección registrada';
    }

    public function obtenerSvgCodigoBarras(string $codigo): string
    {
        return (new BarcodeGeneratorSVG)->getBarcode(
            $codigo,
            BarcodeGeneratorSVG::TYPE_CODE_39,
            2.0,
            70.0
        );
    }

    /** @return array<string, mixed> */
    public function ejecutar(Persona $persona): array
    {
        $codigo = $this->obtenerCodigo($persona);
        $fotoUrl = $this->obtenerUrlFoto($persona);

        $hotelIconRaw = config('hotel.icon', '');
        $hotelIcon = is_string($hotelIconRaw) ? trim($hotelIconRaw) : '';

        $fotoPath = '';
        if ($persona->colaborador?->imagen?->url) {
            $fotoPath = storage_path('app/public/'.$persona->colaborador->imagen->url);
        } elseif ($hotelIcon !== '') {
            $fotoPath = public_path($hotelIcon);
        }

        $fotoBase64 = '';
        if ($fotoPath !== '' && is_file($fotoPath)) {
            $type = pathinfo($fotoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fotoPath);
            if (is_string($data)) {
                $fotoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }

        return [
            'nombre_completo' => $this->obtenerNombreCompleto($persona),
            'codigo' => $codigo,
            'url_foto' => $fotoUrl,
            'foto_base64' => $fotoBase64,
            'logo_base64' => HotelInfo::getLogoBase64(),
            'hotel_icon_base64' => HotelInfo::getIconBase64(),
            'cargo' => $this->obtenerCargoActual($persona),
            'departamento' => $this->obtenerDepartamentoActual($persona),
            'tipo_sangre' => $this->obtenerTipoSangre($persona),
            'direccion' => $this->obtenerDireccion($persona),
            'barcode_svg' => $this->obtenerSvgCodigoBarras($codigo),
        ];
    }
}
