# AGENTS.md - Hotel Bugambilias

## Quick Commands

```bash
composer test          # Run tests (clears config first)
composer pint          # Laravel Pint linting
composer phpstan       # PHPStan level 6 (needs 1GB memory)
composer dev           # Full dev: PHP server + queue + Vite
```

## Architecture

- **Framework**: Laravel 13 + Filament 5 admin panel
- **Admin panel**: `/admin` path, brand "Hotel Bugambilias"
- **Testing**: Pest PHP 4.x

## Filament Resource Structure

Resources auto-discovered from `app/Filament/Resources/`. Each resource follows:

```
app/Filament/Resources/{Group}/{ResourceName}/
├── {ResourceName}Resource.php   # Main resource class
├── Schemas/                    # Form schemas
│   └── {ResourceName}Form.php
├── Tables/                     # Table configs
│   └── {ResourceName}Table.php
└── Pages/                     # Page classes
    ├── List{Name}.php
    ├── Create{Name}.php
    ├── Edit{Name}.php
    └── View{Name}.php
```

## Critical Filament 5 Quirks

**Navigation properties cannot be overridden with union types in PHP 8.2+.** Override methods instead:

```php
// WRONG - causes fatal error
protected static string | UnitEnum | null $navigationGroup = 'My Group';

// CORRECT - override the getter method
public static function getNavigationGroup(): ?string
{
    return 'Gestión de Colaboradores';
}

public static function getNavigationIcon(): ?string
{
    return 'heroicon-o-user-group';
}
```

## Model Namespaces

```
App\Models\Colaboradores\        # Colaborador, ColaboradorDatosMedicos, etc.
App\Filament\Resources\Colaboradores\  # Resource classes
```

## Use Cases Pattern

Base classes in `app/UseCases/Base/` to avoid duplication:
- `BaseCreateUseCase`, `BaseUpdateUseCase`, `BaseDeleteUseCase`

## PHPStan

- Level 6, analyzes `app/`, `routes/`, `database/`
- Config: `phpstan.neon` (includes Larastan extension)

## Common Gotchas

- `ColaboradorForm` has two schema methods: `getRegistroInicialSchema()` and `getEdicionCompletaSchema()`
- Page class files sometimes get stray `$` at end of class declarations - always run `php -l` after editing
- Use `->configure($table)` not `::configure($table)` for table methods
- Filament form methods: `Schema $schema` parameter, return `$schema->components(...)`
