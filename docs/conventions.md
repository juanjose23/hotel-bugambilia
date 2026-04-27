# Convenciones del Proyecto Laravel

Este documento define las convenciones oficiales del equipo para mantener el código ordenado, consistente y fácil de escalar.

## Convencion base Laravel (heredada de Ruby on Rails)

- Modelos: singular (`User`, `Order`, `Product`).
- Tablas: plural (`users`, `orders`, `products`).
- Clases: singular (`UserService`, `OrderRepository`, `CreateUserAction`).
- Relaciones Eloquent: singular o plural segun corresponda al tipo de relacion.
  - Singular para `belongsTo` y `hasOne` (`user`, `profile`).
  - Plural para `hasMany` y `belongsToMany` (`orders`, `roles`).

## Convencion de idioma

- Todo el proyecto debe estar en espanol.
- Esto aplica a documentacion, comentarios, mensajes de negocio, nombres de modulos y comunicacion interna del equipo.
- Solo se permiten terminos en ingles cuando son palabras reservadas del lenguaje, nombres propios del framework o estandares tecnicos.

## 1. Convenciones de nombres de archivos

### Clases

- Usar PascalCase.
- Ejemplos:
  - `UserService.php`
  - `OrderRepository.php`
  - `CreateUserAction.php`

### Modelos

- Singular y PascalCase.
- Ejemplos:
  - `User.php`
  - `Order.php`
  - `Product.php`

### Controllers

- Terminar siempre en `Controller`.
- Ejemplos:
  - `UserController.php`
  - `OrderController.php`
  - `AuthController.php`

### Services

- Sufijo `Service`.
- Ejemplos:
  - `UserService.php`
  - `OrderService.php`
  - `ReportService.php`

### Repositories

- Sufijo `Repository` e interfaces con `Interface`.
- Ejemplos:
  - `UserRepository.php`
  - `OrderRepository.php`
  - `UserRepositoryInterface.php`

### Pipelines y Actions

- Sufijos `Pipeline` y `Action`.
- Ejemplos:
  - `CreateUserPipeline.php`
  - `ValidateOrderAction.php`
  - `SendInvoiceAction.php`

## 2. Convenciones de variables

### Variables

- Usar camelCase.
- Ejemplos:
  - `$userName`
  - `$totalPrice`
  - `$orderItems`

### Booleanos

- Usar prefijos claros.
- Ejemplos:
  - `$isActive`
  - `$hasPermission`
  - `$canEdit`

### Arrays

- Nombres descriptivos en camelCase.
- Ejemplos:
  - `$userData`
  - `$orderList`
  - `$productItems`

## 3. Convenciones de métodos

- Usar camelCase y verbos claros.
- Ejemplos:
  - `createUser()`
  - `updateOrder()`
  - `deleteProduct()`
  - `calculateTotal()`

### Métodos booleanos

- Prefijos semánticos.
- Ejemplos:
  - `isActive()`
  - `hasPermission()`
  - `canAccess()`

## 4. Convenciones de base de datos

### Tablas

- Plural en snake_case.
- Ejemplos:
  - `users`
  - `orders`
  - `order_items`
  - `blog_posts`

### Columnas

- snake_case.
- Ejemplos:
  - `first_name`
  - `last_name`
  - `created_at`
  - `updated_at`

### Foreign keys

- Patrón `<modelo>_id` en singular.
- Ejemplos:
  - `user_id`
  - `order_id`
  - `product_id`

## 5. Convenciones para rutas

- Recursos en plural y snake/kebab simple.
- Ejemplos:
  - `/users`
  - `/orders`
  - `/products`

### API REST

- `GET /users`
- `POST /users`
- `PUT /users/{id}`
- `DELETE /users/{id}`

## 6. Convenciones para constantes

- Usar UPPER_SNAKE_CASE.
- Ejemplos:
  - `MAX_LOGIN_ATTEMPTS`
  - `DEFAULT_USER_ROLE`
  - `CACHE_TTL`

## 7. Convenciones para enums

- Nombre en PascalCase y casos en UPPER_SNAKE_CASE.
- Ejemplos:
  - `UserStatus`
  - `OrderStatus`
  - `PaymentMethod`

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
```

## 8. Convenciones de carpetas

Estructura recomendada en `app/`:

```text
app
├── Models
├── Services
├── Repositories
├── DTO
├── Pipelines
├── Actions
├── Policies
└── Traits
```

## 9. Convenciones para requests

- Usar Form Requests para validación.
- Ejemplos:
  - `StoreUserRequest.php`
  - `UpdateOrderRequest.php`
  - `LoginRequest.php`

## 10. Convenciones para migraciones

- Seguir nombres descriptivos de Laravel.
- Ejemplos:
  - `create_users_table`
  - `create_orders_table`
  - `add_status_to_orders_table`

## 11. Convenciones para eventos

- Nombre en pasado y PascalCase.
- Ejemplos:
  - `UserRegistered`
  - `OrderCreated`
  - `PaymentCompleted`

## 12. Convenciones para jobs

- Nombre en verbo + objeto y sufijo `Job`.
- Ejemplos:
  - `SendWelcomeEmailJob`
  - `ProcessPaymentJob`
  - `GenerateReportJob`

## 13. Convenciones para tests

- Nombre del sujeto + tipo + `Test`.
- Ejemplos:
  - `UserServiceTest`
  - `OrderRepositoryTest`
  - `AuthControllerTest`

## 14. Convenciones de commits

- Usar Conventional Commits.
- Ejemplos:
  - `feat: agregar modulo de usuarios`
  - `fix: corregir error en login`
  - `refactor: mejorar servicio de pedidos`
  - `test: agregar pruebas para OrderService`

## 15. Reglas importantes del equipo

1. Controllers solo manejan request/response.
2. Services contienen la logica de negocio.
3. Repositories acceden a base de datos.
4. Models solo representan datos y relaciones.

### Flujo arquitectonico

```text
Controller
   ↓
Service
   ↓
Repository
   ↓
Model
```

## 16. Herramientas para mantener las convenciones

- Laravel Pint: formateo de codigo.
- PHPStan: analisis estatico y deteccion de errores.
- PHPUnit/Pest: pruebas automatizadas.

## Recomendacion de adopcion

- Aplicar estas reglas en nuevas funcionalidades.
- Refactorizar codigo legado gradualmente.
- Revisar estas convenciones en PRs y code reviews.
