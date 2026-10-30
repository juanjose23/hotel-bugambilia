<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

final class ArchivosTesoreria
{
    public function guardar(UploadedFile $archivo): string
    {
        Validator::make(['archivo' => $archivo], ['archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']])->validate();
        $ruta = $archivo->store('tesoreria/evidencias', 'private');
        if ($ruta === false) {
            throw new DomainException('No se pudo conservar el comprobante. Intenta de nuevo.');
        }

        return $ruta;
    }

    public function huella(string $ruta): string
    {
        if (! str_starts_with($ruta, 'tesoreria/') || str_contains($ruta, '..') || ! Storage::disk('private')->exists($ruta)) {
            throw new DomainException('Adjunta un comprobante privado válido.');
        }
        $disk = Storage::disk('private');
        $mime = $disk->mimeType($ruta);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true) || $disk->size($ruta) > 5 * 1024 * 1024) {
            throw new DomainException('El comprobante debe ser imagen o PDF de hasta 5 MB.');
        }

        return hash('sha256', $disk->get($ruta) ?? '');
    }
}
