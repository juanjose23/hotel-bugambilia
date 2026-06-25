<?php

namespace App\UseCases\Colaboradores\Queries;

use App\Enums\Personas\TipoSangre;
use App\Models\Personas\Persona;
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
            2,
            70
        );
    }

    /** @return array<string, mixed> */
    public function ejecutar(Persona $persona): array
    {
        $codigo = $this->obtenerCodigo($persona);
        $fotoUrl = $this->obtenerUrlFoto($persona);

        $fotoPath = $persona->colaborador?->imagen?->url
            ? storage_path('app/public/'.$persona->colaborador->imagen->url)
            : public_path('img/hotel-icon.png');

        $fotoBase64 = '';
        if (file_exists($fotoPath)) {
            $type = pathinfo($fotoPath, PATHINFO_EXTENSION);
            $data = (string) file_get_contents($fotoPath);
            $fotoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
        }

        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = (string) file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,'.base64_encode($logoData);
        }

        $hotelIconPath = public_path('img/hotel-icon.png');
        $hotelIconBase64 = '';
        if (file_exists($hotelIconPath)) {
            $iconData = (string) file_get_contents($hotelIconPath);
            $hotelIconBase64 = 'data:image/png;base64,'.base64_encode($iconData);
        }

        return [
            'nombre_completo' => $this->obtenerNombreCompleto($persona),
            'codigo' => $codigo,
            'url_foto' => $fotoUrl,
            'foto_base64' => $fotoBase64,
            'logo_base64' => $logoBase64,
            'hotel_icon_base64' => $hotelIconBase64,
            'cargo' => $this->obtenerCargoActual($persona),
            'departamento' => $this->obtenerDepartamentoActual($persona),
            'tipo_sangre' => $this->obtenerTipoSangre($persona),
            'direccion' => $this->obtenerDireccion($persona),
            'barcode_svg' => $this->obtenerSvgCodigoBarras($codigo),
        ];
    }
}
