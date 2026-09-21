<?php

declare(strict_types=1);

namespace Database\Seeders\Clientes;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra una masa de clientes realista y determinista (sin azar).
 *
 * Genera clientes naturales con nombres, apellidos, ciudades, cédulas y
 * teléfonos de corte nicaragüense, más clientes jurídicos (empresas con RUC).
 * Es idempotente: verifica por número de identificación antes de insertar.
 */
final class ClientesMasivoSeeder extends Seeder
{
    private const TOTAL_NATURALES = 120;

    private const TOTAL_JURIDICAS = 12;

    public function run(): void
    {
        $regular = Catalogo::where('codigo', 'CLI_REGULAR')->first();
        $vip = Catalogo::where('codigo', 'CLI_VIP')->first();
        $corporativo = Catalogo::where('codigo', 'CLI_CORPORATIVO')->first();

        if ($regular === null) {
            return;
        }

        $paisId = $this->paisId();

        $this->crearNaturales($regular->id, $vip?->id, $paisId);
        $this->crearJuridicas($corporativo->id ?? $regular->id, $paisId);

        $this->command->info('Clientes masivos ('.self::TOTAL_NATURALES.' naturales y '.self::TOTAL_JURIDICAS.' jurídicos) sembrados.');
    }

    private function crearNaturales(int $catalogoRegular, ?int $catalogoVip, ?int $paisId): void
    {
        $nombresM = [
            'Juan', 'Carlos', 'Luis', 'Pedro', 'Jose', 'Jorge', 'Marco', 'Andres', 'Fernando',
            'Miguel', 'Roberto', 'Manuel', 'Oscar', 'Rafael', 'Enrique', 'Ricardo', 'Pablo',
            'Victor', 'Hugo', 'Denis', 'Wilson', 'Byron', 'Junior', 'Emmanuel', 'Erick',
            'Marlon', 'Darwin', 'Rene', 'Aldo', 'Santiago',
        ];
        $nombresF = [
            'Maria', 'Ana', 'Luz', 'Karla', 'Rosa', 'Marta', 'Carmen', 'Luisana', 'Paola',
            'Beatriz', 'Cristina', 'Yanira', 'Gloria', 'Sonia', 'Marisol', 'Tatiana', 'Gabriela',
            'Victoria', 'Laura', 'Josefa', 'Miriam', 'Elsa', 'Ivania', 'Kenia', 'Marjorie',
            'Hazel', 'Brenda', 'Nubia', 'Thelma', 'Xiomara',
        ];
        $apellidos = [
            'Garcia', 'Martinez', 'Lopez', 'Hernandez', 'Gonzalez', 'Perez', 'Rodriguez',
            'Sanchez', 'Ramirez', 'Cruz', 'Flores', 'Reyes', 'Ortega', 'Vega', 'Molina',
            'Castillo', 'Jimenez', 'Mendoza', 'Rios', 'Blanco', 'Aguilar', 'Navarro',
            'Delgado', 'Vargas', 'Chavez', 'Marin', 'Espinoza', 'Campos', 'Valdivia',
            'Acosta', 'Zelaya', 'Urbina', 'Sandoval', 'Palacios', 'Escobar', 'Sequeira',
            'Galeano', 'Diaz',
        ];
        $ciudades = [
            'Managua', 'Masaya', 'Granada', 'Leon', 'Esteli', 'Matagalpa', 'Chinandega',
            'Jinotega', 'Rivas', 'Boaco', 'Juigalpa', 'Ocotal', 'Somoto', 'Bluefields',
            'Puerto Cabezas', 'Diriamba', 'Jinotepe', 'Tipitapa', 'Ciudad Sandino',
            'El Crucero', 'Nandaime', 'Nagote',
        ];

        DB::transaction(function () use ($nombresM, $nombresF, $apellidos, $ciudades, $catalogoRegular, $catalogoVip, $paisId): void {
            foreach (range(1, self::TOTAL_NATURALES) as $i) {
                $sexo = ($i % 2 === 0) ? 'F' : 'M';
                $nombres = $sexo === 'M' ? $nombresM : $nombresF;

                $primerNombre = $nombres[$i % count($nombres)];
                $segundoNombre = $nombres[($i * 3) % count($nombres)];
                $apellido1 = $apellidos[$i % count($apellidos)];
                $apellido2 = $apellidos[($i * 5) % count($apellidos)];

                $dia = (($i * 3) % 28) + 1;
                $mes = (($i * 5) % 12) + 1;
                $anio = 1963 + (($i * 7) % 34);
                $fechaNacimiento = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

                $cedula = sprintf(
                    '%s-%s-%s%s',
                    $this->departamentoCodigo($i),
                    sprintf('%02d%02d%02d', $dia, $mes, $anio % 100),
                    str_pad((string) (1001 + $i), 4, '0', STR_PAD_LEFT),
                    $this->letraCedula($i)
                );

                if (PersonaNatural::where('numero_identificacion', $cedula)->exists()) {
                    continue;
                }

                $telefono = sprintf(
                    '+505 %04d %04d',
                    8000 + $i,
                    (($i * 37) % 9000) + 1000
                );
                $direccion = sprintf(
                    '%s - Residencial %s #%d',
                    $ciudades[$i % count($ciudades)],
                    $apellidos[($i * 11) % count($apellidos)],
                    $i
                );

                $persona = Persona::create([
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'pais_id' => $paisId,
                    'tipo_persona' => 'natural',
                    'telefono' => $telefono,
                    'direccion' => $direccion,
                ]);

                PersonaNatural::create([
                    'persona_id' => $persona->id,
                    'primer_apellido' => $apellido1,
                    'segundo_apellido' => $apellido2,
                    'tipo_identificacion' => 'cedula',
                    'numero_identificacion' => $cedula,
                    'sexo' => $sexo,
                    'fecha_nacimiento' => $fechaNacimiento,
                ]);

                $catalogoCliente = ($catalogoVip !== null && $i % 20 === 0) ? $catalogoVip : $catalogoRegular;

                Cliente::create([
                    'persona_id' => $persona->id,
                    'catalogo_id' => $catalogoCliente,
                    'estado' => 1,
                ]);
            }
        });
    }

    private function crearJuridicas(int $catalogoCorporativo, ?int $paisId): void
    {
        $empresas = [
            ['nombre' => 'Distribuidora del Pacifico S.A.', 'razon' => 'Distribuidora del Pacifico Sociedad Anonima'],
            ['nombre' => 'Agroexportadora Masaya S.A.', 'razon' => 'Agroexportadora Masaya Sociedad Anonima'],
            ['nombre' => 'Central de Carnes y Abastos S.A.', 'razon' => 'Central de Carnes y Abastos Sociedad Anonima'],
            ['nombre' => 'Textiles del Norte S.A.', 'razon' => 'Textiles del Norte Sociedad Anonima'],
            ['nombre' => 'Lacteos Bugambilia S.A.', 'razon' => 'Lacteos Bugambilia Sociedad Anonima'],
            ['nombre' => 'Transportes Ruta Pacifico S.A.', 'razon' => 'Transportes Ruta Pacifico Sociedad Anonima'],
            ['nombre' => 'Constructora Andina Nica S.A.', 'razon' => 'Constructora Andina Nica Sociedad Anonima'],
            ['nombre' => 'Cafe y Derivados Esteli S.A.', 'razon' => 'Cafe y Derivados Esteli Sociedad Anonima'],
            ['nombre' => 'Ferreteria Industrial Nica S.A.', 'razon' => 'Ferreteria Industrial Nica Sociedad Anonima'],
            ['nombre' => 'Soluciones Turisticas del Lago S.A.', 'razon' => 'Soluciones Turisticas del Lago Sociedad Anonima'],
            ['nombre' => 'Granja Avicola Centro S.A.', 'razon' => 'Granja Avicola Centro Sociedad Anonima'],
            ['nombre' => 'Bebidas y Refrescos del Pacifico S.A.', 'razon' => 'Bebidas y Refrescos del Pacifico Sociedad Anonima'],
        ];

        DB::transaction(function () use ($empresas, $catalogoCorporativo, $paisId): void {
            foreach ($empresas as $i => $empresa) {
                $tipoNet = $i % 3 === 0 ? 'nit' : 'ruc';
                $numero = sprintf(
                    '%s%s%s',
                    $tipoNet === 'ruc' ? 'J0' : 'N0',
                    $this->departamentoCodigo($i),
                    str_pad((string) (5000 + ($i * 11)), 8, '0', STR_PAD_LEFT)
                );

                if (PersonaJuridica::where('numero_identificacion', $numero)->exists()) {
                    continue;
                }

                $persona = Persona::create([
                    'primer_nombre' => $empresa['nombre'],
                    'pais_id' => $paisId,
                    'tipo_persona' => 'juridica',
                    'telefono' => sprintf('+505 2%03d %04d', 200 + $i, (($i * 29) % 9000) + 1000),
                    'direccion' => sprintf('Zona Franca y Plaza Comercial, %s', $this->ciudadParaJuridica($i)),
                ]);

                PersonaJuridica::create([
                    'persona_id' => $persona->id,
                    'razon_social' => $empresa['razon'],
                    'tipo_identificacion' => $tipoNet,
                    'numero_identificacion' => $numero,
                    'fecha_constitucion' => sprintf('200%d-%02d-%02d', 1 + ($i % 9), (($i * 3) % 12) + 1, (($i * 5) % 27) + 1),
                ]);

                Cliente::create([
                    'persona_id' => $persona->id,
                    'catalogo_id' => $catalogoCorporativo,
                    'estado' => 1,
                ]);
            }
        });
    }

    private function departamentoCodigo(int $seed): string
    {
        $codigosDepto = [
            '001', '061', '351', '451', '151', '701', '201', '801', '101', '301',
            '411', '731', '402', '202', '521', '031', '251', '541',
        ];

        return $codigosDepto[$seed % count($codigosDepto)];
    }

    private function letraCedula(int $seed): string
    {
        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr($letras, $seed % 26, 1);
    }

    private function ciudadParaJuridica(int $seed): string
    {
        $ciudades = [
            'Managua', 'Masaya', 'Granada', 'Leon', 'Esteli', 'Matagalpa', 'Chinandega',
            'Rivas', 'Boaco', 'Juigalpa', 'Ocotal', 'Somoto', 'Tipitapa',
        ];

        return $ciudades[$seed % count($ciudades)];
    }

    private function paisId(): ?int
    {
        $paisId = DB::table('paises')->where('codigo_iso2', 'NI')->value('id');

        if (is_numeric($paisId)) {
            return (int) $paisId;
        }

        $fallback = DB::table('paises')->orderBy('id')->value('id');

        return is_numeric($fallback) ? (int) $fallback : null;
    }
}
