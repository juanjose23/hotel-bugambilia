# Arquitectura Use Case para el Hotel

Este documento define el estandar oficial para disenar y construir funcionalidades del Hotel usando Use Case Driven Development en Laravel.

## 1. Metodologia de diseno

Usamos Use Case Driven Development.

Principio:

- El sistema se diseña alrededor de casos de uso del negocio.
- Cada accion importante del dominio vive en una clase de Use Case.

Ejemplos:

- CrearPersona
- RegistrarCliente
- RegistrarColaborador
- AsignarCargo
- RegistrarSalario

## 2. Arquitectura recomendada

Aplicamos una Clean Architecture simplificada con cuatro capas:

- Presentation
- Application
- Domain
- Infrastructure

Estructura sugerida en Laravel:

```text
app
├── Domain
│   ├── Models
│   └── Enums
│
├── UseCases
│   └── Colaboradores
│       ├── CrearColaborador.php
│       ├── ActualizarColaborador.php
│       └── CambiarSalario.php
│
├── Http
│   ├── Controllers
│   └── Requests
│
├── Policies
└── Support
```

Flujo de ejecucion:

```text
Controller
   ↓
UseCase
   ↓
Model
```

## 3. Patrones de diseno

Usar solo los patrones necesarios para mantener bajo boilerplate.

Patrones recomendados:

- Use Case / Interactor: logica de negocio.
- Active Record: modelos Eloquent.
- Factory: creacion de objetos complejos.
- Strategy: comportamientos variables.
- DTO: transporte de datos entre capas.

Patrones a evitar (salvo necesidad real):

- Repository pattern generico.
- Exceso de interfaces.
- Builders innecesarios.
- DDD completo para modulos pequenos.

## 4. Convenciones de nombres

### Casos de uso

- `CrearCliente`
- `ActualizarCliente`
- `EliminarCliente`
- `RegistrarPago`
- `AsignarCargo`

### Modelos

- Singular:
  - `Persona`
  - `Cliente`
  - `Colaborador`
  - `Proveedor`

### Tablas

- Plural:
  - `personas`
  - `clientes`
  - `colaboradores`
  - `proveedores`

### Relaciones Eloquent

- Singular para `belongsTo` y `hasOne`.
- Plural para `hasMany` y `belongsToMany`.

## 5. Ejemplo base

Use Case:

```php
<?php

namespace App\UseCases\Colaboradores;

use App\Models\Colaboradores\Colaborador;

class CrearColaborador
{
    public function execute(array $data): Colaborador
    {
        return Colaborador::create($data);
    }
}
```

Controller:

```php
<?php

namespace App\Http\Controllers;

use App\UseCases\Colaboradores\CrearColaborador;
use Illuminate\Http\Request;

class ColaboradorController extends Controller
{
    public function store(Request $request, CrearColaborador $useCase)
    {
        return $useCase->execute($request->all());
    }
}
```

## 6. Arquitectura modular recomendada

Para ERP/SaaS, organizar por modulos funcionales:

```text
app
├── Domain
│   ├── Models
│   │   ├── Persona
│   │   ├── Cliente
│   │   ├── Colaborador
│   │   └── Proveedor
│
├── UseCases
│   ├── Personas
│   ├── Clientes
│   ├── Colaboradores
│   └── Proveedores
│
├── Http
│   ├── Controllers
│   └── Requests
│
└── Support
```

Beneficios:

- Separa la logica de negocio.
- Escala por modulo sin acoplar todo.
- Facilita mantenimiento a largo plazo.
- Funciona bien con Laravel + Filament.

## 7. Reglas del equipo

1. Cada accion de negocio relevante debe tener su Use Case.
2. Controllers solo orquestan request/response.
3. La validacion va en Form Requests.
4. El modelo representa datos y relaciones.
5. Evitar abstracciones extras sin justificacion.
6. Todo debe estar documentado en espanol.
