<?php

declare(strict_types=1);

// app/UseCases/Shared/Queries/ObtenerNombrePersona.php

namespace App\UseCases\Shared\Queries;

use App\Models\Personas\Persona;

/**
 * Use Case compartido: resuelve el nombre legible de una Persona
 * sin importar si es natural o jurídica.
 *
 * Casos de uso:
 *   - Selects / options de Proveedor, Colaborador, Cliente, etc.
 *   - Cabeceras de documentos (cotizaciones, órdenes, facturas).
 *   - Columnas de tablas Filament que muestran el titular.
 *
 * Lógica:
 *   - Natural  → primer_nombre [segundo_nombre] primer_apellido [segundo_apellido]
 *                (usando el accessor full_name de PersonaNatural)
 *   - Jurídica → razon_social (desde PersonaJuridica)
 *   - Fallback → primer_nombre de la tabla personas (siempre presente)
 */
class ObtenerNombrePersona
{
    /**
     * Obtiene el nombre legible de una Persona por su ID.
     *
     * @return string Nombre completo resuelto, nunca vacío.
     */
    public function execute(int $personaId): string
    {
        /** @var Persona|null $persona */
        $persona = Persona::with(['personaNatural', 'personaJuridica'])->find($personaId);

        if ($persona === null) {
            return "Persona #{$personaId}";
        }

        return self::resolverNombre($persona);
    }

    /**
     * Obtiene el nombre legible a partir de una instancia ya cargada.
     * Útil cuando el modelo ya está en memoria (evita consulta extra).
     */
    public function ejecutar(Persona $persona): string
    {
        return self::resolverNombre($persona);
    }

    /**
     * Método estático para uso directo sin inyección de dependencias.
     *
     * Ejemplo: ObtenerNombrePersona::desde($persona)
     */
    public static function desde(Persona $persona): string
    {
        return self::resolverNombre($persona);
    }

    /**
     * Resuelve el nombre según el tipo de persona.
     */
    private static function resolverNombre(Persona $persona): string
    {
        // --- PERSONA JURÍDICA ---
        if ($persona->tipo_persona === 'juridica') {
            $juridica = $persona->relationLoaded('personaJuridica')
                ? $persona->personaJuridica
                : $persona->personaJuridica()->first();

            if ($juridica !== null && filled($juridica->razon_social)) {
                return trim($juridica->razon_social);
            }
        }

        // --- PERSONA NATURAL ---
        $natural = $persona->personaNatural ?? ($persona->relationLoaded('personaNatural')
            ? $persona->personaNatural
            : $persona->personaNatural()->first());

        if ($natural !== null) {
            // Usa el accessor getFullNameAttribute() ya definido en PersonaNatural
            $fullName = $natural->full_name;

            if (filled($fullName)) {
                return $fullName;
            }
        }

        // --- FALLBACK: primer_nombre + segundo_nombre de tabla personas ---
        $partes = array_filter([
            $persona->primer_nombre ?? '',
            $persona->segundo_nombre ?? '',
        ]);

        $fallback = trim(implode(' ', $partes));

        return filled($fallback) ? $fallback : "Persona #{$persona->id}";
    }
}
