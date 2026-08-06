# AGENTS.md — Hotel Bugambilias

## Reglas de Arquitectura y Desarrollo

### Laravel 13 + Filament 5 + Inertia React

---

# 1. Objetivo del documento

Este documento define las reglas obligatorias de desarrollo del proyecto Hotel Bugambilias.

Toda nueva funcionalidad debe respetar:

- Separación de responsabilidades.
- Arquitectura orientada a casos de uso.
- Código mantenible y testeable.
- Reglas de dominio aisladas.
- Bajo acoplamiento entre capas.
- Uso correcto de Laravel, Filament e infraestructura.

No se deben crear soluciones rápidas que mezclen lógica de negocio con componentes visuales, modelos o controladores.

---

# 2. Stack tecnológico

## Backend

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire
- Spatie Permission
- Filament Shield

## Frontend

- Inertia.js v3
- React 19
- TypeScript
- TailwindCSS v4
- shadcn/ui
- lucide-react

## Base de datos

- PostgreSQL en producción
- SQLite permitido para pruebas

## Calidad de código

- PHPStan nivel 9
- Pest 4
- Laravel Pint
- ESLint
- Prettier

---

# 3. Regla principal de arquitectura

La aplicación sigue una arquitectura por capas.

El flujo permitido es:

```
Filament / Controller
        |
        v
Interactor
        |
        v
BusinessLogic
        |
        v
Repository
        |
        v
Model
```

La respuesta vuelve:

```
Model
 |
Repository
 |
Interactor
 |
Presenter
 |
UI
```

Las capas superiores pueden conocer las inferiores.

Las capas inferiores NO deben conocer las superiores.

Ejemplo incorrecto:

```
BusinessLogic
    -> Filament
    -> Notification
    -> Request HTTP
```

Ejemplo correcto:

```
Filament
    -> Interactor
        -> BusinessLogic
            -> Repository
```

---

# 4. Ubicación de Modelos

Los modelos NO viven en:

```
app/Models
```

Los modelos viven en:

```
app/Repository/Models
```

Ejemplo:

```
App\Repository\Models\Inventario\Producto
App\Repository\Models\Compras\OrdenCompra
App\Repository\Models\Habitaciones\Habitacion
```

Los modelos representan:

- Entidades del dominio.
- Relaciones Eloquent.
- Casts.
- Scopes simples.

Los modelos NO deben contener:

- Procesos complejos.
- Flujos completos.
- Reglas de aprobación.
- Generación de documentos.

---

# 5. Interactors

Ubicación:

```
app/Interactors
```

Responsabilidad:

Representan acciones completas del sistema.

Ejemplos:

```
CrearSolicitudCompra
AprobarOrdenCompra
RegistrarRecepcion
AsignarActivo
GenerarInventarioFisico
```

Un Interactor coordina:

- Validaciones.
- Reglas de negocio.
- Repositorios.
- Eventos.
- Auditoría.

Ejemplo:

```php
final class AprobarSolicitud
{
    public function ejecutar(Solicitud $solicitud): void
    {
        $this->validarEstado->handle($solicitud);

        $this->repository->aprobar($solicitud);

        event(new SolicitudAprobada($solicitud));
    }
}
```

No debe contener:

- Código HTML.
- Filament.
- Queries complejas.
- Formateo de respuestas.

---

# 6. BusinessLogic

Ubicación:

```
app/BusinessLogic
```

Aquí viven las reglas del negocio.

Ejemplos:

```
CanAsignarActivo
ValidarDisponibilidadHabitacion
CalcularCostoServicio
ResolverEstadoSolicitud
ValidarMovimientoStock
```

Debe ser código independiente de:

- Laravel HTTP.
- Filament.
- Livewire.
- Base de datos.

Una regla complicada siempre debe extraerse.

Evitar:

```php
if($estado == 3 && $usuario->rol == 'admin')
```

Preferir:

```php
$this->puedeAprobarSolicitud->validar($solicitud);
```

---

# 7. Repository

Ubicación:

```
app/Repository
```

Separación:

## Lectura

```
Repository/Queries
```

Ejemplo:

```
ProductoQuery
ReporteInventarioQuery
HabitacionesDisponiblesQuery
```

## Escritura

```
Repository/Persistencia
```

Ejemplo:

```
CrearOrdenCompraRepository
RegistrarMovimientoStockRepository
```

Los Interactors no deben hacer:

```php
Producto::query()
```

directamente.

Debe pasar por Repository.

---

# 8. Actions

Ubicación:

```
app/Actions
```

Usar para operaciones pequeñas y reutilizables.

Ejemplos:

```
GenerarCodigoBarras
CrearArchivoPDF
ResolverImagenProducto
ExportarCSV
```

Regla:

Si representa un proceso completo:

```
Interactor
```

Si es una operación puntual:

```
Action
```

---

# 9. Filament

Ubicación:

```
app/Filament
```

Filament es solamente la capa administrativa.

Debe contener:

```
Resources
Pages
Schemas
Tables
RelationManagers
Widgets
```

Regla:

Filament NO contiene lógica de negocio.

Incorrecto:

```php
->action(function(){
   Producto::where(...)->update();
});
```

Correcto:

```php
->action(function(){
    app(ActualizarProducto::class)
        ->ejecutar($producto);
});
```

Filament solamente:

- Captura datos.
- Muestra información.
- Ejecuta interactors.

---

# 10. Notificaciones

Ubicación:

```
app/Notifications
```

Las notificaciones se dividen por dominio:

```
Notifications/
    Compras/
    Inventario/
    Reportes/
```

Ejemplo:

```
ReporteListo
OrdenCompraEmitida
StockMinimoAlcanzado
```

La lógica de decidir quién recibe una notificación pertenece a:

```
BusinessLogic
o
Interactor
```

No dentro de la Notification.

---

# 11. Jobs

Ubicación:

```
app/Jobs
```

Usar para procesos:

- Pesados.
- Largos.
- Segundo plano.
- Generación de archivos.
- Correos masivos.

Ejemplos:

```
GenerarReporteJob
ProcesarImportacionJob
EnviarNotificacionesJob
```

Un Job NO debe tener lógica compleja.

Debe llamar:

```
Job
 |
Interactor
 |
Action
```

Ejemplo:

```php
public function handle()
{
    app(GenerarReporte::class)
        ->ejecutar($this->data);
}
```

---

# 12. Eventos y Listeners

Eventos:

```
app/Events
```

Representan algo ocurrido.

Ejemplo:

```
OrdenCompraCreada
ActivoAsignado
StockActualizado
```

Listeners:

```
app/Listeners
```

Ejecutan consecuencias:

- Notificar.
- Auditar.
- Sincronizar.

---

# 13. Enums

Ubicación:

```
app/Enums
```

Usar para:

- Estados.
- Tipos.
- Categorías cerradas.

Ejemplo:

```php
EstadoSolicitud::APROBADA
```

No usar números mágicos:

Incorrecto:

```php
if($estado == 3)
```

Correcto:

```php
if($estado === EstadoSolicitud::Aprobada)
```

---

# 14. Reportes

Los reportes siguen esta estructura:

```
Interactor
    |
    Action Generadora
    |
    Layout PDF
    |
    View Blade
```

Nunca generar PDF directamente desde Filament.

---

# 15. Código obligatorio

Todo código nuevo debe cumplir:

- Tipado estricto:

```php
declare(strict_types=1);
```

- Clases finales cuando corresponda:

```php
final class CrearSolicitud
```

- Constructor injection.

Evitar:

```php
app(MiServicio::class)
```

Excepto casos puntuales.

Preferir:

```php
public function __construct(
    private MiServicio $servicio
){}
```

---

# 16. Calidad

Antes de hacer commit:

Ejecutar:

```
composer lint
composer phpstan
composer test
```

No aceptar:

- Código duplicado.
- Métodos gigantes.
- Reglas de negocio en Filament.
- Queries SQL dentro de vistas.
- Modelos con demasiada responsabilidad.

---

# 17. Organización por dominio

Cada módulo debe mantenerse agrupado:

Ejemplo:

```
Compras
 ├── Interactors
 ├── BusinessLogic
 ├── Repository
 ├── Notifications
 ├── Events
 └── Filament
```

Los módulos principales:

```
Activos
Catalogos
Compras
Inventario
Limpieza
Habitaciones
Servicios
Usuarios
```

---

# Regla final

Antes de crear una clase preguntarse:

¿Es una pantalla?

→ Filament

¿Es una acción completa del usuario?

→ Interactor

¿Es una regla del negocio?

→ BusinessLogic

¿Es acceso a datos?

→ Repository

¿Es una operación pequeña reutilizable?

→ Action

¿Es procesamiento en segundo plano?

→ Job

¿Es una reacción a algo ocurrido?

→ Event + Listener

Esta regla mantiene el proyecto escalable y evita mezclar responsabilidades.
