<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use DomainException;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Str;

trait DatosTesoreria
{
    /**
     * @param  array<string,mixed>  $data
     */
    private function texto(array $data, string $campo): string
    {
        $v = $data[$campo] ?? null;
        if (! is_string($v)) {
            throw new DomainException('Revisa el campo '.$campo.'.');
        }

        return $v;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function numero(array $data, string $campo): float
    {
        $v = $data[$campo] ?? null;
        if (! is_numeric($v)) {
            throw new DomainException('Revisa el importe.');
        }

        return (float) $v;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function idDato(array $data, string $campo): int
    {
        $v = $data[$campo] ?? null;
        if (! is_numeric($v) || (int) $v <= 0 || (float) $v !== (float) (int) $v) {
            throw new DomainException('Selecciona una opción válida.');
        }

        return (int) $v;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function idOpcional(array $data, string $campo): ?int
    {
        return empty($data[$campo]) ? null : $this->idDato($data, $campo);
    }

    private function claveOperacion(): Hidden
    {
        return Hidden::make('clave')->default(fn (): string => Str::uuid()->toString())->required();
    }

    private function soporte(): FileUpload
    {
        return FileUpload::make('soporte')->label('Comprobante privado')->disk('private')->directory('tesoreria/administracion')->visibility('private')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])->maxSize(5120)->required();
    }

    private function permitido(string $permiso): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can($permiso);
    }
}
