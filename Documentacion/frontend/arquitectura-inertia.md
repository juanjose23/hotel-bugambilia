# Arquitectura Inertia React

## Convención de páginas

Todas las páginas que Laravel puede renderizar viven en `resources/js/pages` y exportan un componente predeterminado.

```text
resources/js/pages/{seccion}/{Pagina}.tsx
```

El resolvedor de `app.tsx` utiliza un único `import.meta.glob('./pages/**/*.tsx')`. No existe fallback ni una segunda ubicación de páginas.

## Organización de módulos

`resources/js/modules` contiene la implementación interna reutilizable:

```text
modules/{seccion}/
  components/
  hooks/
  tipos/
  pages/   # implementación interna transitoria, nunca resolución directa de Inertia
```

Las entradas bajo `resources/js/pages` pueden delegar en componentes internos del módulo. Esto mantiene una única convención para Inertia sin duplicar componentes.

## Controladores públicos

Cada sección pública tiene un controlador dedicado:

- `LandingController`
- `Habitaciones/HabitacionController`
- `Espacios/EspacioController`
- `Servicios/ServicioController`
- `Restaurante/RestauranteController`
- `Reservas/MisReservasController`
- `Publico/AcercaDeController`
- `Publico/ContactoController`
- `Publico/FavoritosController`
- `Publico/PagoController`

Los controladores coordinan Request, Interactor y `Inertia::render`. Las consultas y reglas de dominio continúan fuera del controlador.

## Reglas obligatorias

- Las rutas no renderizan páginas mediante closures.
- Cada página tiene `export default`.
- La navegación interna usa `Link` o `router` de Inertia.
- Los formularios usan `Form` o `useForm`.
- Los tipos compartidos amplían los módulos de Inertia mediante imports reales, sin reemplazar sus declaraciones.
- Los nombres enviados a `Inertia::render()` coinciden con una ruta dentro de `resources/js/pages`.
