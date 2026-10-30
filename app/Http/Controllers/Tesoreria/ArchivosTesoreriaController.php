<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Repository\Models\User;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ArchivosTesoreriaController extends Controller
{
    public function __construct(private TesoreriaQuery $query) {}

    public function archivo(Request $request, string $tipo, int $id): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $ruta = $this->query->archivo($user, $tipo, $id);
        abort_unless(Storage::disk('private')->exists($ruta), 404);

        return Storage::disk('private')->response($ruta, 'comprobante.'.pathinfo($ruta, PATHINFO_EXTENSION), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'], str_starts_with(Storage::disk('private')->mimeType($ruta) ?: '', 'image/') ? 'inline' : 'attachment');
    }

    public function auditoria(Request $request): StreamedResponse
    {
        abort_unless(config('tesoreria.habilitado') === true, 404);
        Gate::authorize('Auditar:Fondos');

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }fputcsv($out, ['Origen', 'Movimiento', 'Fecha', 'Fondo / turno', 'Moneda', 'Usuario', 'Importe', 'Concepto', 'Referencia'], ',', '"', '');
            foreach ($this->query->auditoria() as $fila) {
                $fila = array_map(static fn (string|int|float|null $v): string|int|float|null => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v, $fila);
                fputcsv($out, $fila, ',', '"', '');
            }fclose($out);
        }, 'auditoria-tesoreria.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
