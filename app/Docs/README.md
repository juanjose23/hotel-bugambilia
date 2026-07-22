# Arquitectura de `app/`

Esta carpeta documenta la organización nueva y deja claro qué capa hace cada trabajo.

## Orden recomendado

1. `Interactors/`
2. `BusinessLogic/`
3. `Repository/`
4. `Models/` dentro de `app/Repository/Models`
5. `Presenters/`
6. `Services/`
7. `Actions/`
8. `Exports/`
9. `Imports/`
10. `Jobs/`
11. `Enums/`
12. `Traits/`
13. `Events/`
14. `Listeners/`
15. `Exceptions/`
16. `Notifications/`
17. `Docs/`

## Función de cada capa

### `Interactors/`

Contiene los casos de uso. Aquí vive la intención del sistema: crear, actualizar, cancelar, aprobar, consultar, etc.

### `BusinessLogic/`

Agrupa las reglas de negocio puras. No debe depender de HTTP, Inertia o la base de datos.

Aquí van las reglas centrales del dominio, por ejemplo cálculos, validaciones de estado, restricciones operativas y decisiones que pueden reutilizarse en más de un caso de uso. Si una regla es muy específica de un flujo puntual, puede vivir en su `interactor` correspondiente.

### `Repository/`

Encapsula el acceso a datos. Aquí se consulta y persiste a través de los modelos ubicados en `app/Repository/Models`, para que los controladores e interactores no hablen directo con Eloquent.

### `Models/` en `app/Repository/Models`

Representa las entidades del dominio y la persistencia Eloquent. Los modelos viven aquí, no dentro de `app/Models`.

### `Presenters/`

Convierte la salida a la forma que necesita la UI, JSON, API o Inertia.

### `Services/`

Agrupa integraciones externas y utilidades reutilizables, como correo, pagos, archivos o APIs de terceros.

### `Actions/`

Contiene clases de propósito único con una sola responsabilidad técnica o de negocio pequeña. Si una operación es reusable y corta, va aquí. Si representa un caso de uso completo, debe vivir en `interactors/`.

### `Reglas` dentro de `BusinessLogic/`

Cuando una regla sea muy específica y compleja, conviene dividirla en clases pequeñas dentro de `BusinessLogic/`, por ejemplo `ValidateRoomAvailability`, `CanAssignUserToShift` o `CalculatePenalty`. Así la lógica no termina metida en un interactor gigante.

### `Exports/`

Contiene clases de exportación de datos, por ejemplo para Excel, CSV o archivos planos. Estas clases transforman información para descargarla o enviarla fuera del sistema.

### `Imports/`

Contiene clases de importación de datos, por ejemplo para procesar Excel, CSV o cargas masivas.

### `Jobs/`

Contiene trabajos en cola o tareas diferidas. Úsala para procesos asíncronos, envíos, sincronizaciones o tareas largas que no deben bloquear la petición HTTP.

### `Traits/`

Contiene traits reutilizables de PHP. Úsalos para compartir comportamiento entre modelos, servicios o clases que realmente comparten una misma responsabilidad técnica.

### `Enums/`

Contiene enumeraciones de PHP para estados, tipos, roles o valores cerrados del dominio. Si el valor representa una decisión de negocio o una lista finita, va aquí. Si solo es una constante técnica, puede quedarse en `config/` o en una clase de soporte.

### `Events/`

Contiene eventos de dominio o de aplicación. Úsalos para anunciar que algo importante ocurrió, por ejemplo `UserCreated`, `InventoryAdjusted` o `RoomStatusChanged`.

### `Listeners/`

Contiene los listeners que reaccionan a los eventos. Aquí va la respuesta técnica a un evento: enviar una notificación, sincronizar datos, registrar auditoría o ejecutar otra acción.

### `Exceptions/`

Contiene excepciones personalizadas del dominio o de la aplicación. Úsalas para errores explícitos de negocio y para centralizar cómo se reportan o renderizan.

### `Notifications/`

Contiene las clases de notificación de Laravel. Aquí van correos, SMS, notificaciones de base de datos o canales personalizados. `service/` puede ayudar a construir o enviar datos, pero no debe alojar las clases de notificación.

### `Docs/`

Contiene la documentación técnica de la arquitectura, decisiones y convenciones.

## Flujo correcto

`controller` -> `interactor` -> `business_logic` -> `repository` -> `model`

Luego la salida vuelve por `presenter` hacia la respuesta final.

## Limpieza de Laravel por defecto

La carpeta antigua `app/Http/Controllers` ya no se usa en esta arquitectura. `app/Http` queda solo para middleware u otros componentes realmente necesarios.

La pantalla inicial por defecto de Laravel también debe reemplazarse por una página o entrada propia del proyecto cuando se defina el flujo principal.
