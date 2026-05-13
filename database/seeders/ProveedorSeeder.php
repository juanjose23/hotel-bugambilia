<?php

namespace Database\Seeders;

use App\Models\Compras\Proveedor;
use App\Models\Compras\ProveedorContacto;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaJuridica;
use App\UseCases\Compras\GenerarCodigoProveedor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $paisNicaragua = 558;

        $tipoNacional = DB::table('catalogos')
            ->where('codigo', 'PROV_NACIONAL')
            ->value('id');
        $tipoInternacional = DB::table('catalogos')
            ->where('codigo', 'PROV_INTERNACIONAL')
            ->value('id');

        $proveedores = [
            [
                'razon_social' => 'Distribuidora de Alimentos del Pacífico S.A.',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000212345',
                'nombre_corto' => 'Dist. Alimentos Pacífico',
                'tipo_proveedor_id' => $tipoNacional,
                'direccion_fiscal' => 'Km 10.5 Carretera Sur, Managua',
                'contacto_principal' => ['Carlos Méndez', 'Gerente de Ventas', '+505 8888-1001', 'cmendez@alimentospacifico.com.ni'],
                'contacto_secundario' => ['María López', 'Coordinadora de Pedidos', '+505 7777-2001', 'mlopez@alimentospacifico.com.ni'],
            ],
            [
                'razon_social' => 'Lencería y Textiles Hotelera Nicaragüense S.A.',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000323456',
                'nombre_corto' => 'Lencería Hotelera Nica',
                'tipo_proveedor_id' => $tipoNacional,
                'direccion_fiscal' => 'Del Policio 1c al Sur, 1c al Oeste, Managua',
                'contacto_principal' => ['Ana Carolina Rivas', 'Directora Comercial', '+505 8888-2002', 'arivas@lenceriahotelera.com.ni'],
                'contacto_secundario' => ['Pedro Ramírez', 'Logística y Entregas', '+505 7777-3002', 'pramirez@lenceriahotelera.com.ni'],
            ],
            [
                'razon_social' => 'Químicos y Suministros Profesionales S.A.',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000434567',
                'nombre_corto' => 'Químicos Profesionales',
                'tipo_proveedor_id' => $tipoNacional,
                'direccion_fiscal' => 'Costado Este Mercado Mayoreo, Managua',
                'contacto_principal' => ['Roberto Gutiérrez', 'Gerente de Cuentas Hoteleras', '+505 8888-3003', 'rgutierrez@quimipro.com.ni'],
                'contacto_secundario' => ['Sofía Martínez', 'Atención al Cliente', '+505 7777-4003', 'smartinez@quimipro.com.ni'],
            ],
            [
                'razon_social' => 'Bebidas y Licores Selectos S.A.',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000545678',
                'nombre_corto' => 'Bebidas Selectas',
                'tipo_proveedor_id' => $tipoNacional,
                'direccion_fiscal' => 'Rotonda El Güegüense 2c al Sur, Managua',
                'contacto_principal' => ['Fernando Castillo', 'Ejecutivo de Ventas', '+505 8888-4004', 'fcastillo@bebidaselectas.com.ni'],
                'contacto_secundario' => ['Gabriela Talavera', 'Administración', '+505 7777-5004', 'gtalavera@bebidaselectas.com.ni'],
            ],
            [
                'razon_social' => 'Equipos y Mobiliario Hotelero de Centroamérica',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000656789',
                'nombre_corto' => 'Equipos Hoteleros CA',
                'tipo_proveedor_id' => $tipoInternacional,
                'direccion_fiscal' => 'Avenida La Prensa, Edificio Corporativo, Managua',
                'contacto_principal' => ['Ricardo Duarte', 'Gerente Regional', '+505 8888-5005', 'rduarte@equiposhotelerosca.com'],
                'contacto_secundario' => ['Laura Vega', 'Coordinadora de Proyectos', '+505 7777-6005', 'lvega@equiposhotelerosca.com'],
            ],
            [
                'razon_social' => 'Lavandería Industrial La Colina S.A.',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000767890',
                'nombre_corto' => 'Lavandería La Colina',
                'tipo_proveedor_id' => $tipoNacional,
                'direccion_fiscal' => 'Carretera a Masaya Km 7.5, Managua',
                'contacto_principal' => ['Jorge Sandino', 'Gerente de Operaciones', '+505 8888-6006', 'jsandino@lacolina.com.ni'],
                'contacto_secundario' => ['Carmela Flores', 'Servicio al Cliente', '+505 7777-7006', 'cflores@lacolina.com.ni'],
            ],
        ];

        foreach ($proveedores as $item) {
            $persona = Persona::create([
                'primer_nombre' => $item['nombre_corto'],
                'segundo_nombre' => null,
                'pais_id' => $paisNicaragua,
                'tipo_persona' => 'juridica',
                'telefono' => $item['contacto_principal'][2],
                'direccion' => $item['direccion_fiscal'],
            ]);

            PersonaJuridica::create([
                'persona_id' => $persona->id,
                'razon_social' => $item['razon_social'],
                'tipo_identificacion' => $item['tipo_identificacion'],
                'numero_identificacion' => $item['numero_identificacion'],
            ]);

            $codigo = app(GenerarCodigoProveedor::class)->ejecutar();
            $proveedor = Proveedor::create([
                'codigo' => $codigo,
                'persona_id' => $persona->id,
                'tipo_proveedor_id' => $item['tipo_proveedor_id'],
                'direccion_fiscal' => $item['direccion_fiscal'],
                'estado' => 1,
            ]);

            ProveedorContacto::create([
                'proveedor_id' => $proveedor->id,
                'nombre' => $item['contacto_principal'][0],
                'cargo' => $item['contacto_principal'][1],
                'telefono' => $item['contacto_principal'][2],
                'email' => $item['contacto_principal'][3],
                'principal' => true,
            ]);

            ProveedorContacto::create([
                'proveedor_id' => $proveedor->id,
                'nombre' => $item['contacto_secundario'][0],
                'cargo' => $item['contacto_secundario'][1],
                'telefono' => $item['contacto_secundario'][2],
                'email' => $item['contacto_secundario'][3],
                'principal' => false,
            ]);
        }
    }
}
