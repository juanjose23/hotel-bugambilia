# Enums del Módulo Restaurante

## Descripción General

Los enums del módulo Restaurante definen los estados, tipos y categorías cerradas del dominio. Residen en `app/Enums/Restaurante/` y siguen el patrón de Laravel backed enums.

## Lista de Enums

### 1. EstadoCuentaRestaurante

**Ubicación**: `app/Enums/Restaurante/EstadoCuentaRestaurante.php`

**Tipo**: String Backed Enum

**Descripción**: Define los estados por los que puede pasar una cuenta de restaurante.

#### Valores

| Case | Value | Label | Color Badge | Icon |
|------|-------|-------|-------------|------|
| `ABIERTA` | `abierta` | Abierta | warning | heroicon-o-folder-open |
| `EN_PROCESO` | `en_proceso` | En Proceso | info | heroicon-o-arrow-path |
| `PAGADA` | `pagada` | Pagada | success | heroicon-o-check-circle |
| `CANCELADA` | `cancelada` | Cancelada | danger | heroicon-o-x-circle |

#### Flujo de Estados

```
ABIERTA → EN_PROCESO → PAGADA
         ↓
      CANCELADA
```

#### Uso

```php
use App\Enums\Restaurante\EstadoCuentaRestaurante;

$cuenta->estado = EstadoCuentaRestaurante::ABIERTA;

if ($cuenta->estado === EstadoCuentaRestaurante::PAGADA) {
    // Lógica para cuentas pagadas
}
```

---

### 2. EstadoPedido

**Ubicación**: `app/Enums/Restaurante/EstadoPedido.php`

**Tipo**: String Backed Enum

**Descripción**: Define los estados por los que puede pasar un pedido de restaurante.

#### Valores

| Case | Value | Label | Color Badge | Icon |
|------|-------|-------|-------------|------|
| `ABIERTO` | `abierto` | Abierto | warning | heroicon-o-folder-open |
| `EN_PREPARACION` | `en_preparacion` | En Preparación | info | heroicon-o-fire |
| `LISTO` | `listo` | Listo | success | heroicon-o-check |
| `SERVIDO` | `servido` | Servido | primary | heroicon-o-check-circle |
| `PAGADO` | `pagado` | Pagado | success | heroicon-o-banknotes |
| `CARGADO_A_HABITACION` | `cargado_a_habitacion` | Cargado a Habitación | purple | heroicon-o-home |
| `CANCELADO` | `cancelado` | Cancelado | danger | heroicon-o-x-circle |

#### Flujo de Estados

```
ABIERTO → EN_PREPARACION → LISTO → SERVIDO → PAGADO
                ↓
            CANCELADO

ABIERTO → CARGADO_A_HABITACION → PAGADO
```

#### Uso

```php
use App\Enums\Restaurante\EstadoPedido;

$pedido->estado = EstadoPedido::ABIERTO;
$pedido->estado = EstadoPedido::EN_PREPARACION;

if ($pedido->estado === EstadoPedido::PAGADO) {
    // Lógica para pedidos pagados
}

// En Filament tables
Badge::make('estado')
    ->color(fn ($state) => $state->getColor())
    ->icon(fn ($state) => $state->getIcon());
```

---

### 3. EstadoItemPedido

**Ubicación**: `app/Enums/Restaurante/EstadoItemPedido.php`

**Tipo**: String Backed Enum

**Descripción**: Define los estados de un item individual dentro de un pedido.

#### Valores

| Case | Value | Label | Color Badge |
|------|-------|-------|-------------|
| `PENDIENTE` | `pendiente` | Pendiente | gray |
| `PREPARACION` | `preparacion` | En Preparación | info |
| `LISTO` | `listo` | Listo | success |
| `SERVIDO` | `servido` | Servido | primary |
| `ANULADO` | `anulado` | Anulado | danger |

#### Flujo de Estados

```
PENDIENTE → PREPARACION → LISTO → SERVIDO
                ↓
            ANULADO
```

#### Uso

```php
use App\Enums\Restaurante\EstadoItemPedido;

$item->estado = EstadoItemPedido::PENDIENTE;

// Excluir items anulados de cálculos
if ($item->estado !== EstadoItemPedido::ANULADO) {
    $subtotal += $item->subtotal;
}
```

---

### 4. CategoriaPlato

**Ubicación**: `app/Enums/Restaurante/CategoriaPlato.php`

**Tipo**: String Backed Enum

**Descripción**: Define las categorías del menú del restaurante.

#### Valores

| Case | Value | Label |
|------|-------|-------|
| `Entradas` | `REST_ENTRADAS` | Entradas |
| `Platos` | `REST_PLATOS` | Platos Fuertes |
| `Postres` | `REST_POSTRES` | Postres |
| `Bebidas` | `REST_BEBIDAS` | Bebidas |
| `General` | `RESTAURANTE` | General |

#### Métodos

```php
// Obtener label
CategoriaPlato::Entradas->label(); // "Entradas"

// Obtener array de opciones para selects
CategoriaPlato::options(); 
// ['REST_ENTRADAS' => 'Entradas', 'REST_PLATOS' => 'Platos Fuertes', ...]

// Obtener array de códigos
CategoriaPlato::codigos();
// ['REST_ENTRADAS', 'REST_PLATOS', 'REST_POSTRES', 'REST_BEBIDAS', 'RESTAURANTE']
```

#### Uso

```php
use App\Enums\Restaurante\CategoriaPlato;

$plato->categoria_id = Catalogo::where('codigo', CategoriaPlato::Entradas->value)->first()->id;

// En Filament selects
Select::make('categoria_id')
    ->options(CategoriaPlato::options())
    ->default(CategoriaPlato::General->value);
```

---

### 5. AreaCocina

**Ubicación**: `app/Enums/Restaurante/AreaCocina.php`

**Tipo**: String Backed Enum

**Descripción**: Define las áreas de cocina donde se preparan los platos. Se usa para routing de comandas a impresoras específicas.

#### Valores

| Case | Value | Label | Color Badge | Icon |
|------|-------|-------|-------------|------|
| `COCINA` | `cocina` | Cocina Principal | warning | heroicon-o-fire |
| `BAR` | `bar` | Barra / Tragos | info | heroicon-o-glass-water |
| `POSTRES` | `postres` | Postres & Repostería | success | heroicon-o-sparkles |
| `PARRILLA` | `parrilla` | Parrilla & Carnes | danger | heroicon-o-beaker |

#### Uso

```php
use App\Enums\Restaurante\AreaCocina;

$plato->area_cocina = AreaCocina::COCINA;

// Determinar impresora según área
$impresora = match ($plato->area_cocina) {
    AreaCocina::COCINA => 'Termica_Cocina',
    AreaCocina::BAR => 'Termica_Bar',
    AreaCocina::POSTRES => 'Termica_Postres',
    AreaCocina::PARRILLA => 'Termica_Parrilla',
};
```

---

### 6. AccionAuditoriaRestaurante

**Ubicación**: `app/Enums/Restaurante/AccionAuditoriaRestaurante.php`

**Tipo**: String Backed Enum

**Descripción**: Define las acciones que se auditan en el módulo restaurante.

#### Valores

| Case | Value | Label |
|------|-------|-------|
| `CambioEstadoMesa` | `CAMBIO_ESTADO_MESA` | Cambio Estado Mesa |
| `MoverCuentaMesa` | `MOVER_CUENTA_MESA` | Mover Cuenta Mesa |
| `AplicarDescuento` | `APLICAR_DESCUENTO` | Aplicar Descuento |
| `ImprimirComanda` | `IMPRIMIR_COMANDA` | Imprimir Comanda |
| `ReimprimirComanda` | `REIMPRIMIR_COMANDA` | Reimprimir Comanda |
| `GuardarConfiguracion` | `GUARDAR_CONFIGURACION_RESTAURANTE` | Guardar Configuración |

#### Uso

```php
use App\Enums\Restaurante\AccionAuditoriaRestaurante;

$auditoria->registrar(
    accion: AccionAuditoriaRestaurante::CambioEstadoMesa,
    mesaId: $mesaId,
    detalles: ['nuevo_estado' => $estado->getLabel()],
    userId: auth()->id(),
    ipAddress: request()->ip(),
);
```

---

### 7. EstadoCuentaRestaurante

**Ubicación**: `app/Enums/Restaurante/EstadoCuentaRestaurante.php`

**Tipo**: String Backed Enum

**Descripción**: Define los estados por los que puede pasar una cuenta de restaurante.

#### Valores

| Case | Value | Label | Color Badge | Icon |
|------|-------|-------|-------------|------|
| `ABIERTA` | `abierta` | Abierta | warning | heroicon-o-folder-open |
| `EN_PROCESO` | `en_proceso` | En Proceso | info | heroicon-o-arrow-path |
| `PAGADA` | `pagada` | Pagada | success | heroicon-o-check-circle |
| `CANCELADA` | `cancelada` | Cancelada | danger | heroicon-o-x-circle |

#### Flujo de Estados

```
ABIERTA → EN_PROCESO → PAGADA
         ↓
      CANCELADA
```

#### Uso

```php
use App\Enums\Restaurante\EstadoCuentaRestaurante;

$cuenta->estado = EstadoCuentaRestaurante::ABIERTA;

if ($cuenta->estado === EstadoCuentaRestaurante::PAGADA) {
    // Lógica para cuentas pagadas
}
```

---

### 8. MetodoPago

**Ubicación**: `app/Enums/Restaurante\MetodoPago.php`

**Tipo**: String Backed Enum

**Descripción**: Define los métodos de pago aceptados en el restaurante.

#### Valores

| Case | Value | Label | Color Badge | Icon |
|------|-------|-------|-------------|------|
| `EFECTIVO` | `efectivo` | Efectivo | success | heroicon-o-banknotes |
| `TARJETA_CREDITO` | `tarjeta_credito` | Tarjeta de Crédito | info | heroicon-o-credit-card |
| `TARJETA_DEBITO` | `tarjeta_debito` | Tarjeta de Débito | info | heroicon-o-credit-card |
| `TRANSFERENCIA` | `transferencia` | Transferencia | warning | heroicon-o-arrows-right-left |
| `QR` | `qr` | QR | primary | heroicon-o-qr-code |
| `CORTESIA` | `cortesia` | Cortesía | gray | heroicon-o-gift |

#### Uso

```php
use App\Enums\Restaurante\MetodoPago;

$pago->metodo_pago = MetodoPago::TARJETA_CREDITO;

// En Filament selects
Select::make('metodo_pago')
    ->options(MetodoPago::class)
    ->default(MetodoPago::EFECTIVO);
```

---

### 9. TipoTicketComanda

**Ubicación**: `app/Enums/Restaurante/TipoTicketComanda.php`

**Tipo**: String Backed Enum

**Descripción**: Define los tipos de tickets de comanda que se pueden imprimir.

#### Valores

| Case | Value | Label |
|------|-------|-------|
| `ORIGINAL` | `original` | Comanda Original |
| `REIMPRESION` | `reimpresion` | Reimpresión |
| `COPIA` | `copia` | Copia |

#### Uso

```php
use App\Enums\Restaurante\TipoTicketComanda;

// Generar URL de impresión
$url = route('admin.restaurante.comanda', [
    'pedido' => $pedido->id,
    'tipo' => TipoTicketComanda::REIMPRESION->value,
    'area' => $area,
]);
```

---

## Patrón de Diseño

Todos los enums siguen este patrón:

```php
<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NombreEnum: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case CASE_1 = 'valor_1';
    case CASE_2 = 'valor_2';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASE_1 => 'Label 1',
            self::CASE_2 => 'Label 2',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CASE_1 => 'success',
            self::CASE_2 => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASE_1 => 'heroicon-o-check',
            self::CASE_2 => 'heroicon-o-x',
        };
    }
}
```

## Interfaces Implementadas

### HasLabel
Proporciona el método `getLabel()` para obtener el texto legible del enum. Usado en Filament para mostrar en selects y badges.

### HasColor
Proporciona el método `getColor()` para obtener el color de Filament (success, danger, warning, info, primary, gray).

### HasIcon
Proporciona el método `getIcon()` para obtener el icono de Heroicons.

## Trait TieneAyudantesEnum

Trait compartido que proporciona métodos auxiliares comunes para todos los enums:

- `toArray()`: Convierte el enum a array
- `values()`: Obtiene todos los valores del enum
- `names()`: Obtiene todos los nombres (cases) del enum

## Reglas

- **Strict Types**: Todos usan `declare(strict_types=1)`
- **Backed Enums**: Son string-backed enums (no unit enums)
- **Match Expressions**: Usan match para implementar métodos
- **Interfaces**: Implementan interfaces de Filament cuando es necesario
- **No Magic Numbers**: Evitan el uso de números mágicos en el código
- **Type Safety**: Permiten type hinting en parámetros y retornos

## Ejemplo de Migración de Código Antiguo

**Incorrecto (números mágicos)**:
```php
if ($pedido->estado == 3) {
    // Estado pagado
}
```

**Correcto (con enums)**:
```php
use App\Enums\Restaurante\EstadoPedido;

if ($pedido->estado === EstadoPedido::PAGADO) {
    // Estado pagado
}
```
