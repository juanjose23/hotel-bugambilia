# Documentacion del Proyecto

Esta carpeta centraliza los estandares internos del proyecto.

## Contenido

- [Convenciones de desarrollo](./conventions.md)
- [Arquitectura Use Case](./use-case-architecture.md)

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
