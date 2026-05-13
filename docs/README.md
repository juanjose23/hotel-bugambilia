# Documentación del Sistema - Hotel Bugambilias

Bienvenido a la documentación técnica y operativa. El sistema sigue estándares de arquitectura limpia y seguridad basada en roles (RBAC).

## 🧭 Índice de Documentos Activos

| Archivo | Propósito |
| :--- | :--- |
| [**MODULO_COMPRAS.md**](./MODULO_COMPRAS.md) | **Manual Maestro de Compras (P2P).** Flujo de solicitudes, cotizaciones, órdenes y recepciones. |
| [**INICIALIZACION_SEGURIDAD.md**](./INICIALIZACION_SEGURIDAD.md) | Guía técnica para instalar y configurar roles y permisos con Filament Shield. |
| [**SEGURIDAD.md**](./SEGURIDAD.md) | Políticas generales de seguridad y trazabilidad. |
| [**use-case-architecture.md**](./use-case-architecture.md) | Estándares de programación para la capa de lógica de negocio (UseCases). |

## ⚠️ Archivos Deprecados
Los archivos `COMPRAS.md`, `COTIZACIONES.md` y `solicitudes-compra.md` han sido consolidados en el manual maestro y pueden ser eliminados para evitar confusiones.

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
