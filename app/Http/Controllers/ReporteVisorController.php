<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repository\Models\User;
use Illuminate\Http\Response;

final class ReporteVisorController extends Controller
{
    public function ver(string $token): Response
    {
        // 1. Validar formato seguro del token UUID
        if (! preg_match('/^[a-zA-Z0-9_-]{10,64}$/', $token)) {
            return response()->view('errors.404', [
                'codigo' => 400,
                'badge' => 'Petición Inválida',
                'titulo' => 'Identificador no válido',
                'mensaje' => 'El enlace proporcionado no tiene una estructura de reporte válida.',
            ], 400);
        }

        $filePath = storage_path("app/temp-reportes/{$token}.pdf");
        $ttlConfig = config('reportes.token_ttl_minutos');
        $ttlMinutos = is_int($ttlConfig) ? $ttlConfig : 30;
        $ttlSegundos = $ttlMinutos * 60;

        // 2. Obtener metadatos desde la caché
        /** @var array{filename?: string, codigo?: string, titulo?: string, user_id?: int|null, expires_at?: int}|null $meta */
        $meta = cache()->get("reporte_meta_{$token}");

        // 3. Evaluar expiración física o de caché
        $existeEnDisco = file_exists($filePath);
        $mtime = $existeEnDisco ? (filemtime($filePath) ?: 0) : 0;
        $antiguedadSegundos = $existeEnDisco ? (time() - $mtime) : 0;
        $haExpirado = $meta === null
            || (isset($meta['expires_at']) && now()->timestamp > $meta['expires_at'])
            || ($existeEnDisco && $antiguedadSegundos > $ttlSegundos);

        // Si expiró por tiempo (en caché o en disco)
        if ($haExpirado) {
            if ($existeEnDisco) {
                @unlink($filePath);
            }
            cache()->forget("reporte_meta_{$token}");

            return response()->view('errors.410', [
                'mensaje' => 'El enlace de este reporte ha expirado y el documento temporal fue eliminado por seguridad. Por favor genere uno nuevo desde el panel de reportes.',
            ], 410);
        }

        // Si el archivo físico ya no existe en disco en absoluto
        if (! $existeEnDisco) {
            cache()->forget("reporte_meta_{$token}");

            return response()->view('errors.410', [
                'mensaje' => 'El documento temporal ya no se encuentra disponible en el servidor. Por favor genere uno nuevo desde el panel de reportes.',
            ], 410);
        }

        // 4. Validar autenticación y autorización de usuario
        /** @var User|null $user */
        $user = auth()->user();
        if ($user === null) {
            return response()->view('errors.403', [
                'codigo' => 403,
                'badge' => 'Autenticación Requerida',
                'titulo' => 'Sesión Requerida',
                'mensaje' => 'Debe iniciar sesión en el panel para visualizar este documento.',
            ], 403);
        }

        $reportOwnerId = $meta['user_id'] ?? null;
        if ($reportOwnerId !== null && $reportOwnerId !== $user->id) {
            $roleConfig = config('filament-shield.super_admin.name');
            $superAdminRole = is_string($roleConfig) ? $roleConfig : 'super_admin';
            $esSuperAdmin = $user->hasRole($superAdminRole) || $user->is_admin === true;
            if (! $esSuperAdmin) {
                return response()->view('errors.403', [
                    'codigo' => 403,
                    'badge' => 'Acceso Denegado',
                    'titulo' => 'Reporte No Autorizado',
                    'mensaje' => 'No tiene permisos para visualizar este reporte generado por otro usuario.',
                ], 403);
            }
        }

        $filename = $meta['filename'] ?? "reporte-{$token}.pdf";
        $content = file_get_contents($filePath);

        if ($content === false) {
            return response()->view('errors.404', [
                'codigo' => 404,
                'badge' => 'Error de Lectura',
                'titulo' => 'Documento Inaccesible',
                'mensaje' => 'No se pudo leer el archivo generado del reporte.',
            ], 404);
        }

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
        ]);
    }
}
