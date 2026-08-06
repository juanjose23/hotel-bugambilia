<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\DiagnosticarConciliacionRecetas;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use UnitEnum;

final class ConciliacionRecetasCocina extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Conciliación Recetas';

    protected static ?string $title = 'Conciliación de Recetas de Cocina';

    protected static ?string $slug = 'restaurante/conciliacion-recetas';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.restaurante.conciliacion-recetas-cocina';

    /** @var array{resumen: array<string, int>, items: list<array<string, mixed>>} */
    public array $diagnostico = [
        'resumen' => [],
        'items' => [],
    ];

    public function mount(DiagnosticarConciliacionRecetas $diagnosticar): void
    {
        $this->diagnostico = $diagnosticar->ejecutar();
    }

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volverCocina')
                ->label('Volver a Cocina')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => CocinaPedidos::getUrl()),
            Action::make('crearReglaTransformacion')
                ->label('Nueva regla')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Regla de transformación')
                ->modalWidth('3xl')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                    ])
                        ->schema([
                            Select::make('producto_materia_prima_id')
                                ->label('Materia prima')
                                ->options(fn (): array => self::opcionesProductos())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn (Set $set): mixed => $set('variante_materia_prima_id', null)),

                            Select::make('variante_materia_prima_id')
                                ->label('Variante materia prima')
                                ->options(fn (Get $get): array => self::opcionesVariantes($get('producto_materia_prima_id')))
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('producto_bruto_id')
                                ->label('Material bruto')
                                ->options(fn (): array => self::opcionesProductos())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn (Set $set): mixed => $set('variante_bruta_id', null)),

                            Select::make('variante_bruta_id')
                                ->label('Variante material bruto')
                                ->options(fn (Get $get): array => self::opcionesVariantes($get('producto_bruto_id')))
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('cantidad_bruta')
                                ->label('Cantidad bruta')
                                ->numeric()
                                ->minValue(0.0001)
                                ->required(),

                            TextInput::make('cantidad_resultado')
                                ->label('Cantidad resultado')
                                ->numeric()
                                ->minValue(0.0001)
                                ->required(),

                            TextInput::make('merma_estimada')
                                ->label('Merma estimada')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            Toggle::make('estado')
                                ->label('Activa')
                                ->default(true),

                            Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                ])
                ->action(function (array $data, DiagnosticarConciliacionRecetas $diagnosticar): void {
                    RecetaTransformacionMateriaPrima::query()->create($data);
                    $this->diagnostico = $diagnosticar->ejecutar();

                    Notification::make()
                        ->title('Regla creada')
                        ->success()
                        ->send();
                }),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /** @return array<int, string> */
    private static function opcionesProductos(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Producto::query()
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }

    /** @return array<int, string> */
    private static function opcionesVariantes(mixed $productoId): array
    {
        if (! is_numeric($productoId)) {
            return [];
        }

        return ProductoVariante::query()
            ->where('producto_id', (int) $productoId)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $nombre = $variante->nombre_variante ?: $variante->codigo;

                return [(int) $variante->id => "{$variante->codigo} - {$nombre}"];
            })
            ->all();
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_ConciliacionRecetasCocina') || $user?->can('page_CocinaPedidos') || ($user?->hasRole('super_admin') ?? false);
    }
}
