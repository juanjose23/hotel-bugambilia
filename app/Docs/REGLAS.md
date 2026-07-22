# Reglas de Arquitectura del Proyecto

## 1. Objetivo

Este documento define las reglas de organización, responsabilidades y dependencias permitidas dentro del proyecto.

El objetivo es mantener una arquitectura limpia, escalable y mantenible, separando claramente:

- Presentación (Filament)
- Casos de uso
- Reglas de negocio
- Persistencia
- Integraciones externas
- Procesos asíncronos

La arquitectura busca evitar que la lógica del negocio termine mezclada con componentes visuales, modelos o infraestructura.

---

# 2. Principios generales

## 2.1 Cada clase debe tener una responsabilidad clara

Una clase debe resolver un problema específico.

Evitar clases que:

- creen registros
- envíen notificaciones
- generen archivos
- validen reglas
- cambien estados

Todo al mismo tiempo.

Si una clase comienza a tener demasiadas responsabilidades, debe dividirse.

---

## 2.2 La interfaz nunca contiene lógica de negocio

La capa de presentación solamente debe:

- mostrar información
- capturar datos
- llamar casos de uso
- mostrar resultados

No debe:

- consultar directamente modelos
- ejecutar reglas de negocio
- cambiar estados manualmente
- enviar notificaciones
- generar reportes

Ejemplo incorrecto:

```php
Action::make('aprobar')
    ->action(function(){

        $orden->estado = 'aprobada';
        $orden->save();

        enviarCorreo();

    });
```

Ejemplo correcto:

```php
Action::make('aprobar')
    ->action(function(){

        app(AprobarOrdenCompra::class)
            ->ejecutar($orden);

    });
```

---

# 3. Reglas de Interactors

Ubicación:

```
app/Interactors
```

## Responsabilidad

Los interactors representan acciones completas del sistema.

Responden preguntas como:

- ¿Qué ocurre cuando se crea una compra?
- ¿Qué ocurre cuando se aprueba una solicitud?
- ¿Qué ocurre cuando se registra una recepción?
- ¿Qué ocurre cuando se genera un reporte?

Ejemplos:

```
Interactors/
├── Compras/
│   ├── CrearSolicitud.php
│   ├── AprobarSolicitud.php
│   └── GenerarOrdenCompra.php
│
├── Inventario/
│   ├── AjustarStock.php
│   └── TrasladarProducto.php
```

Un interactor puede coordinar:

- reglas de negocio
- repositorios
- eventos
- servicios

---

# 4. Reglas de BusinessLogic

Ubicación:

```
app/BusinessLogic
```

## Responsabilidad

Contiene reglas puras del negocio.

Debe responder:

"¿La operación es válida?"

Ejemplos:

```
BusinessLogic/

Inventario/
├── PuedeRealizarSalida.php
├── CalcularCostoPromedio.php

Habitaciones/
├── ValidarDisponibilidad.php

Limpieza/
├── PuedeAsignarTurno.php
```

## Restricciones

BusinessLogic NO debe:

- consultar base de datos
- usar modelos Eloquent
- enviar correos
- usar Filament
- depender de HTTP

Ejemplo:

Correcto:

```php
final class PuedeCancelarOrden
{
    public function validar(string $estado): bool
    {
        return $estado === 'pendiente';
    }
}
```

Incorrecto:

```php
OrdenCompra::where()
```

---

# 5. Reglas de Repository

Ubicación:

```
app/Repository
```

## Responsabilidad

Centraliza el acceso a datos.

Debe encargarse de:

- consultas complejas
- persistencia
- filtros
- relaciones

Los interactors no deben hacer:

```php
Producto::query()
```

directamente.

Ejemplo:

```php
$productos =
    $productoRepository->buscarDisponibles();
```

---

# 6. Reglas de Models

Ubicación:

```
app/Repository/Models
```

Los modelos representan:

- tablas
- relaciones
- casts
- scopes simples

Los modelos NO deben contener:

- procesos completos
- envío de correos
- generación de reportes
- reglas complejas

Evitar:

```php
public function aprobar()
{
    cambiarEstado();
    enviarCorreo();
    crearAuditoria();
}
```

Preferir:

```php
AprobarSolicitudInteractor
```

---

# 7. Reglas de Actions

Ubicación:

```
app/Actions
```

Una Action debe realizar una tarea pequeña.

Correcto:

```
GenerarCodigoBarras
CrearArchivoPdf
CalcularHashArchivo
```

Incorrecto:

```
CrearProcesoCompletoCompra
```

Eso pertenece a Interactors.

---

# 8. Reglas de Services

Ubicación:

```
app/Services
```

Los servicios representan integraciones o herramientas externas.

Ejemplos:

```
Services/

Pdf/
Barcode/
Email/
Storage/
Whatsapp/
Api/
```

No usar Services como reemplazo de Interactors.

Incorrecto:

```
CrearProductoService
AprobarCompraService
```

Correcto:

```
CrearProductoInteractor
PdfService
```

---

# 9. Reglas de Jobs

Ubicación:

```
app/Jobs
```

Los Jobs solamente manejan procesos que:

- tardan mucho
- usan colas
- pueden ejecutarse después

Ejemplos:

- generar reportes pesados
- importar archivos
- enviar correos masivos

Un Job NO debe contener lógica del negocio.

Incorrecto:

```
Job
 ├── validar
 ├── guardar
 ├── generar pdf
 └── notificar
```

Correcto:

```
Job
 |
 Interactor
 |
 Servicios
```

---

# 10. Reglas de Events y Listeners

Eventos representan hechos ocurridos.

Ejemplo:

```
OrdenCompraAprobada
ProductoCreado
StockAjustado
```

Listeners representan acciones posteriores:

```
EnviarNotificacionOrdenCompra
RegistrarAuditoria
ActualizarIndicadores
```

Un evento no debe ejecutar lógica.

---

# 11. Reglas de Notifications

Ubicación:

```
app/Notifications
```

Responsabilidad:

- correo
- base de datos
- broadcast
- canales externos

Las notificaciones no deben decidir reglas.

Incorrecto:

```php
if($orden->estado == aprobado)
```

Eso pertenece al negocio.

---

# 12. Reglas de Filament

Filament es únicamente presentación.

Permitido:

- formularios
- tablas
- filtros
- acciones UI
- navegación

No permitido:

- lógica de negocio
- consultas complejas
- procesos completos

Toda operación debe pasar por un Interactor.

---

# 13. Flujo permitido

El flujo recomendado es:

```
Filament
   |
   v
Interactor
   |
   +---- BusinessLogic
   |
   +---- Repository
   |
   +---- Services
   |
   v
Model
```

Para procesos secundarios:

```
Interactor
   |
   v
Event
   |
   v
Listener
   |
   v
Notification / Job
```

---

# 14. Reglas para nombres

Los nombres deben expresar intención.

Correcto:

```
AprobarSolicitudCompra
GenerarReporteInventario
ValidarStockDisponible
```

Evitar:

```
ProcesarDatos
Manager
Helper
Utils
GeneralService
```

---

# 15. Regla final

Antes de crear una clase nueva preguntarse:

1. ¿Es una acción completa del usuario?
   → Interactor

2. ¿Es una regla del negocio?
   → BusinessLogic

3. ¿Es acceso a datos?
   → Repository

4. ¿Es una integración externa?
   → Service

5. ¿Es una tarea pequeña reutilizable?
   → Action

6. ¿Es una tarea pesada o diferida?
   → Job

7. ¿Es una reacción a algo ocurrido?
   → Event + Listener

8. ¿Es interfaz?
   → Filament

Estas reglas deben mantenerse para evitar que la aplicación pierda separación de responsabilidades a medida que crezca.
