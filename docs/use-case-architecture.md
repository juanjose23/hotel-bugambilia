# Arquitectura Use Case — Hotel Bugambilias

Este documento es la **única fuente de verdad** sobre la arquitectura, convenciones y estándares del proyecto. Todo código nuevo debe cumplir estas reglas.

---

## 1. Metodología de diseño

Usamos **Use Case Driven Development**.

- El sistema se diseña alrededor de **casos de uso del negocio** (no de pantallas ni tablas).
- Cada acción importante del dominio vive en su propia clase **Use Case**.
- Las capas siguen una **Clean Architecture simplificada** con 4 niveles.

### Capas oficiales

| Capa | Directorio | Responsabilidad |
|------|-----------|----------------|
| **Presentation** | `Http/Controllers/`, `Filament/Resources/`, `routes/` | Recibir input del usuario, delegar a Use Cases |
| **Application** | `UseCases/`, `Http/Requests/`, `Rules/` | Lógica de negocio, validación, orquestación |
| **Domain** | `Models/`, `Enums/` | Datos, relaciones y reglas del negocio |
| **Infrastructure** | `config/`, `database/`, `Support/` | Persistencia, configuraciones, helpers |

### Flujo de ejecución

```
Controller / Filament Resource
       ↓
    UseCase
       ↓
     Model
```

Los **Controllers** solo orquestan request/response. La lógica de negocio **siempre** va en Use Cases.

---

## 2. Estructura real del proyecto

```
app/
├── Actions/                  # Acciones específicas de Filament/UI
│   └── Catalogos/
│       ├── GenerarEtiquetasCodigosBarrasAction.php
│       └── GenerarReporteProductosAction.php
├── Console/Commands/         # Comandos Artisan personalizados
├── Docs/                     # Documentación técnica (ej. REPORTES.md)
├── Enums/                    # Enums con valores string
│   ├── CatalogoTipo.php
│   ├── EstadoCatalogo.php
│   ├── Sexo.php
│   ├── TipoIdentificacion.php
│   ├── TipoProducto.php
│   ├── TipoSangre.php
│   └── TipoUbicacion.php
├── Exceptions/               # Excepciones personalizadas del dominio
│   ├── AccesoDenegadoException.php
│   ├── ErrorInternoException.php
│   ├── HotelException.php
│   ├── MantenimientoException.php
│   └── RecursoNoEncontradoException.php
├── Filament/Resources/       # Panel admin (auto-descubiertos)
│   ├── Audits/
│   ├── Catalogos/
│   │   ├── Catalogos/
│   │   ├── CatalogoTipos/
│   │   ├── Pais/
│   │   ├── Productos/
│   │   └── Ubicacions/
│   └── Colaboradores/
│       ├── ColaboradorCargoHistorial/
│       ├── ColaboradorContactoEmergencia/
│       ├── ColaboradorDatosMedicos/
│       ├── ColaboradorDocumento/
│       ├── Colaboradors/
│       └── ColaboradorSalario/
├── Http/
│   ├── Controllers/          # Solo request/response (uso mínimo)
│   │   └── Catalogos/
│   └── Requests/             # Form Requests (validación)
│       ├── Colaboradores/
│       └── Personas/
├── Models/                   # Eloquent models
│   ├── Audits/
│   │   └── AuditoriaReporte.php
│   ├── Catalogos/
│   │   ├── Catalogo.php
│   │   ├── CatalogoTipo.php
│   │   ├── Pais.php
│   │   ├── Producto.php
│   │   ├── ProductoVariante.php
│   │   └── Ubicacion.php
│   ├── Colaboradores/
│   │   ├── Colaborador.php
│   │   ├── ColaboradorCargoHistorial.php
│   │   ├── ColaboradorContactoEmergencia.php
│   │   ├── ColaboradorDatosMedicos.php
│   │   ├── ColaboradorDocumento.php
│   │   └── ColaboradorSalario.php
│   ├── General/
│   │   └── Imagen.php
│   ├── Personas/
│   │   ├── Persona.php
│   │   ├── PersonaJuridica.php
│   │   └── PersonaNatural.php
│   └── User.php
├── Providers/                # Service Providers
│   └── Filament/
├── Rules/                    # Reglas de validación personalizadas
│   ├── Colaboradores/
│   └── Personas/
├── Support/                  # Helpers
│   └── ReportePaginador.php
└── UseCases/                 # Lógica de negocio pura
    ├── Base/                 # Base classes (intención: BaseCreateUseCase, etc.)
    ├── Catalogos/
    │   ├── ExportProductosUseCase.php
    │   ├── GenerarCodigoBarrasUseCase.php
    │   └── ImportProductosUseCase.php
    ├── Colaboradores/
    │   ├── CrearNuevoSalario.php
    │   ├── GenerarCodigo.php
    │   ├── ObtenerDatosCarnet.php
    │   └── ObtenerNombreCompleto.php
    ├── Compras/
    │   ├── Solicitudes/
    │   │   ├── AprobarSolicitud.php
    │   │   ├── CancelarSolicitud.php
    │   │   ├── GenerarCodigoSolicitud.php
    │   │   ├── ObtenerSolicitudConItems.php
    │   │   ├── ObtenerSolicitudParaComparativa.php
    │   │   ├── ObtenerSolicitudesParaComparar.php
    │   │   └── RechazarSolicitud.php
    │   ├── Cotizaciones/
    │   │   ├── ActualizarEstadosCotizacionesSolicitud.php
    │   │   ├── AnalizarScoringCotizaciones.php
    │   │   ├── ElegirCotizacionGanadora.php
    │   │   ├── ObtenerCotizacionConItemsProveedor.php
    │   │   ├── ObtenerCotizacionesPorSolicitud.php
    │   │   ├── ObtenerRecomendacionLogistica.php
    │   │   └── SeleccionarItemGanador.php
    │   ├── OrdenesCompra/
    │   │   ├── CancelarOrdenCompra.php
    │   │   ├── EmitirOrdenCompra.php
    │   │   ├── GenerarCodigoOrdenCompra.php
    │   │   ├── GenerarOrdenDesdeCotizacion.php
    │   │   ├── GenerarOrdenesDesdeComparativa.php
    │   │   ├── ObtenerOrdenCompraConItems.php
    │   │   └── VerificarEstadoOrdenCompra.php
    │   ├── Recepciones/
    │   │   ├── GenerarCodigoRecepcion.php
    │   │   └── GestionarTransicionRecepcion.php
    │   ├── Devoluciones/
    │   │   └── Mutations/
    │   │       ├── DevolverMercanciaProveedor.php
    │   │       └── GenerarCodigoDevolucion.php
    │   └── Proveedores/
    │       ├── ActualizarProveedor.php
    │       ├── CrearProveedor.php
    │       └── GenerarCodigoProveedor.php
    └── Reportes/
        ├── RegistrarAuditoriaReporteUseCase.php
        └── RegistrarReporteUseCase.php
```

---

## 3. Action vs UseCase

| | **Action** (`app/Actions/`) | **UseCase** (`app/UseCases/`) |
|---|---|---|
| Propósito | Tareas atómicas vinculadas a la UI (Filament) | Lógica de negocio pura, reutilizable |
| Ejemplo | `GenerarReporteProductosAction` | `ExportProductosUseCase` |
| Dependencias | Puede llamar Use Cases, PDF, Barcode | Modelos, servicios externos |
| Testing | Se prueba integrado con la UI | Se prueba unitariamente |

**Regla:** Un Use Case nunca depende de un Action. Un Action puede llamar Use Cases.

---

## 4. Filament Resources

Cada Resource sigue esta estructura:

```
app/Filament/Resources/{Group}/{ResourceName}/
├── {ResourceName}Resource.php   # Clase principal del Resource
├── Schemas/                     # Form schemas e infolists
│   ├── {ResourceName}Form.php
│   └── {ResourceName}Infolist.php (opcional)
├── Tables/                      # Configuración de tabla
│   └── {ResourceName}Table.php
└── Pages/                       # Páginas del Resource
    ├── List{Name}.php
    ├── Create{Name}.php
    ├── Edit{Name}.php
    └── View{Name}.php
```

Los Resources se auto-descubren desde `app/Filament/Resources/`.

**Convenciones específicas de Filament:**
- Usar `->configure($table)` no `::configure($table)` para métodos de tabla.
- En forms: parámetro `Schema $schema`, retornar `$schema->components(...)`.
- El formulario de `ColaboradorForm` expone dos métodos: `getRegistroInicialSchema()` y `getEdicionCompletaSchema()`.

---

## 5. Convenciones de código

### Idioma

- **Todo en español:** clases, métodos, variables, tablas, columnas, documentación, commits.
- Solo se permite inglés para palabras reservadas del lenguaje, nombres propios del framework o estándares técnicos (`id`, `created_at`, `SoftDeletes`, `Auth`, etc.).

### Nombres de clases y archivos

| Tipo | Convención | Ejemplo |
|------|-----------|---------|
| Modelo | Singular, PascalCase | `Persona`, `Colaborador`, `Producto` |
| Use Case | Verbo + objeto, PascalCase | `CrearColaborador`, `ExportProductosUseCase` |
| Action | Verbo + Action, PascalCase | `GenerarReporteProductosAction` |
| Controller | Sufijo `Controller` | `ColaboradorController` |
| Form Request | Store/Update + entidad | `StoreColaboradorRequest` |
| Enum | PascalCase | `TipoProducto`, `Sexo`, `EstadoCatalogo` |
| Exception | Sufijo `Exception` | `RecursoNoEncontradoException` |
| Migration | Descriptivo snake_case | `create_colaboradores_table` |
| Model relationship | Singular (belongsTo/hasOne), Plural (hasMany/belongsToMany) | `$colaborador->salarios()`, `$colaborador->persona()` |

### Tablas y columnas

- Tablas: plural snake_case (`colaboradores`, `producto_variantes`).
- Columnas: snake_case (`primer_nombre`, `fecha_nacimiento`).
- Foreign keys: `<modelo_singular>_id` (`persona_id`, `producto_id`).

### Variables y métodos

- Variables: `$camelCase`, booleanos con prefijo `is`, `has`, `can`.
- Métodos: `camelCase()` con verbos claros (`crear()`, `obtenerNombreCompleto()`), booleanos con prefijo semántico (`isActivo()`, `hasSalario()`).

### Enums

```php
enum TipoProducto: string
{
    case PERECEDERO = 'perecdero';
    case NO_PERECEDERO = 'no_perecdero';
}
```

Nombre PascalCase, casos UPPER_SNAKE_CASE.

### Constantes

`UPPER_SNAKE_CASE`: `MAX_INTENTOS_LOGIN`, `CACHE_TTL_PRODUCTOS`.

---

## 6. Base de datos

- Migraciones con nombres descriptivos: `create_{tabla}_table`, `add_{columna}_to_{tabla}_table`.
- Usar tipos de columna nativos de Laravel.
- Timestamps `created_at` / `updated_at` automáticos.
- SoftDeletes cuando aplique.

---

## 7. Rutas

- Solo `web.php` y `console.php`.
- El panel admin usa Filament (rutas automáticas en `/admin`).
- Para rutas web tradicionales: resources en plural.
- API REST opcional: `GET /recurso`, `POST /recurso`, `PUT /recurso/{id}`, `DELETE /recurso/{id}`.

---

## 8. Eventos y Jobs

- Eventos: nombre en pasado PascalCase (`ColaboradorCreado`, `ReporteGenerado`).
- Jobs: verbo + objeto + sufijo `Job` (`EnviarCorreoBienvenidaJob`, `ProcesarPagoJob`).

---

## 9. Pruebas

- Framework: **Pest PHP 4.x**.
- Test files: `{Sujeto}Test.php` (ej. `ColaboradorServiceTest.php`).
- Ubicación: `tests/Feature/` o `tests/Unit/`.
- Estrategia: pruebas unitarias para Use Cases, pruebas de feature para Filament Resources.

---

## 10. Commits

Usar **Conventional Commits**:

```
feat: agregar modulo de colaboradores
fix: corregir error al calcular salario
refactor: extraer logica de generacion de codigo
test: agregar pruebas para CrearNuevoSalario
```

---

## 11. Excepciones

Todas las excepciones personalizadas extienden `HotelException`:

- `AccesoDenegadoException` — Permisos insuficientes.
- `RecursoNoEncontradoException` — Entidad no encontrada.
- `ErrorInternoException` — Error inesperado del sistema.
- `MantenimientoException` — Sistema en mantenimiento.

---

## 12. Herramientas oficiales

| Herramienta | Comando | Propósito |
|------------|---------|-----------|
| Laravel Pint | `composer pint` | Formateo de código PHP |
| PHPStan (nivel 6) | `composer phpstan` | Análisis estático |
| Pest PHP | `composer test` | Pruebas unitarias y de integración |
| DomPDF | — | Motor oficial de reportes PDF |
| Maatwebsite Excel | — | Exportación Excel (.xlsx) |

---

## 13. Reglas del equipo

1. Cada acción de negocio relevante debe tener su **Use Case**.
2. Los **Controllers** solo orquestan request/response, sin lógica de negocio.
3. La validación va en **Form Requests**, no en controllers ni modelos.
4. El **Modelo** representa datos y relaciones, nada más.
5. **Actions** son para tareas atómicas de UI que llaman Use Cases.
6. Evitar abstracciones extras sin justificación (no Repository pattern genérico, no exceso de interfaces, no DDD completo para módulos pequeños).
7. Revisar estas reglas en cada Pull Request.
