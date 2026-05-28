# Documentación del Sistema - Hotel Bugambilias

Bienvenido a la documentación técnica y operativa. El sistema sigue estándares de arquitectura limpia y seguridad basada en roles (RBAC).

## 🧭 Índice de Documentos Activos

| Archivo | Propósito |
| :--- | :--- |
| [**MODULO_COMPRAS.md**](./MODULO_COMPRAS.md) | **Manual Maestro de Compras (P2P).** Flujo completo: solicitudes, cotizaciones, órdenes de compra, recepciones, devoluciones e integración con inventario. |
| [**MODULO_INVENTARIO (Carpeta)**](./inventario/README.md) | **Módulo de Inventario Modularizado.** Índice principal que enlaza a la base de datos (`BASE_DATOS.md`), lógicas y reglas de negocio (`FUNCIONALIDADES.md`), y el catálogo de interactores (`CASOS_USO.md`). |
| [**MODULO_INVENTARIO (Legado)**](./MODULO_INVENTARIO.md) | **Documento de Inventario Consolidado.** Arquitectura inicial y desglose de la serie de reportes de inventario HTB-INV-001 a HTB-INV-012. |
| [**PROCESO_UBICACIONES_RECURSIVAS.md**](./PROCESO_UBICACIONES_RECURSIVAS.md) | **Proceso Maestro de Ubicaciones Jerárquicas.** Flujo P2L (Purchase-to-Location) unificado sin activos fijos, algoritmo de secuencias con bloqueo pesimista en base de datos, y reglas multi-moneda de reportes. |
| [**REPORTES_Y_NOTIFICACIONES.md**](./REPORTES_Y_NOTIFICACIONES.md) | Correcciones aplicadas a reportes PDF y sistema de notificaciones del módulo de Compras. |
| [**MODULO_USUARIOS.md**](./MODULO_USUARIOS.md) | Gestión de usuarios: creación, autogeneración de credenciales y asignación de roles. |
| [**MODULO_LIMPIEZA.md**](./MODULO_LIMPIEZA.md) | **Módulo de Gestión y Control de Limpieza.** Flujo polimórfico de limpieza de habitaciones/espacios, notificaciones en tiempo real a camaristas e integración de packs de blancos con inventario FEFO. |
| [**MODULO_HABITACIONES_ESPACIOS.md**](./MODULO_HABITACIONES_ESPACIOS.md) | **Habitaciones, Espacios, Kits y Clonación.** Estructura de habitaciones y espacios recursivos, packs de productos (blancos), asignación polimórfica de activos fijos físicos y reglas de oro de clonación sin duplicación de hardware. |
| [**Seguridad/Configuración**](./seguridad/CONFIGURACION.md) | Guía técnica de roles y permisos con Filament Shield. |
| [**Seguridad/Matriz Acciones**](./seguridad/MATRIZ_ACCIONES.md) | Detalle de acciones por estado y permisos. |
| [**use-case-architecture.md**](./use-case-architecture.md) | Estándares de programación para la capa de lógica de negocio (UseCases). |
| [**Catálogo de Reportes**](../app/Docs/REPORTES.md) | Todos los reportes del sistema con código, concepto y filtros. |

## ⚠️ Archivos Deprecados
Los archivos `SEGURIDAD.md`, `INICIALIZACION_SEGURIDAD.md`, `COMPRAS.md`, `COTIZACIONES.md` y `solicitudes-compra.md` han sido consolidados y deben ser eliminados.

---
*Hotel Bugambilias*

## Definiciones base del proyecto

### Metodologia de diseno

- Use Case Driven Development.
- El diseno parte de casos de uso de negocio y no de pantallas o tablas.

### Arquitectura

- Clean Architecture simplificada.
- Capas oficiales: Presentation, Application, Domain, Infrastructure.
- Flujo base: Controller -> UseCase -> Model.

### Patrones de diseno

- Recomendados: Use Case/Interactor, Active Record, Factory, Strategy, DTO.
- Evitar (si no hay necesidad real): Repository generico, exceso de interfaces, builders innecesarios, DDD completo.

## Objetivo

- Mantener una base de codigo consistente.
- Acelerar onboarding de nuevos integrantes.
- Reducir decisiones repetitivas en implementacion y revisiones.

## Como usar esta documentacion

1. Consultar antes de crear nuevas clases, rutas, migraciones o tests.
2. Validar en cada Pull Request que el codigo cumpla estas reglas.
3. Actualizar la documentacion cuando el equipo acuerde nuevos estandares.

## Mantenimiento

- Responsable sugerido: Juan Huete.
- Frecuencia sugerida: revision mensual o cuando haya cambios de arquitectura.
