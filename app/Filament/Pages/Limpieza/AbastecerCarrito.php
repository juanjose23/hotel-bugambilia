<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Enums\Catalogos\TipoUbicacion;
use App\Interactors\Limpieza\Carrito\CrearCarrito;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Queries\Limpieza\Carrito\ObtenerListadoCarritos;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class AbastecerCarrito extends Page implements HasTable
{
    use HasPageShield, InteractsWithTable;

    protected ObtenerListadoCarritos $listadoCarritos;

    public function boot(ObtenerListadoCarritos $listadoCarritos): void
    {
        $this->listadoCarritos = $listadoCarritos;
    }

    protected string $view = 'filament.resources.limpieza.abastecer-carrito';

    protected static ?string $slug = 'limpieza/abastecer-carrito';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $navigationLabel = 'Administración de Carritos';

    protected static ?string $title = 'Administración de Carritos de Limpieza';

    protected static ?int $navigationSort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->listadoCarritos->execute(Ubicacion::query()->where(function ($q) {
                $q->where('tipo', TipoUbicacion::CARRITO->value)
                    ->orWhere('tipo', 'carrito')
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carrito%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carro%']);
            })))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del Carrito')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción'),
                TextColumn::make('insumos_count')
                    ->label('Cantidad de Insumos')
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('bloqueado')
                    ->label('En Uso')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->headerActions([
                Action::make('crear_carrito')
                    ->label('Crear Carrito')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Carrito')
                            ->placeholder('Ej: Carrito Limpieza C')
                            ->required()
                            ->unique('ubicaciones', 'nombre'),
                        TextInput::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Ej: Carrito móvil para tercer piso'),
                    ])
                    ->action(function (array $data, CrearCarrito $crearCarrito) {
                        $crearCarrito->execute(
                            nombre: $data['nombre'],
                            descripcion: $data['descripcion'] ?? null,
                        );
                        Notification::make()->title('Carrito Creado')->body('El nuevo carrito se ha registrado con éxito.')->success()->send();
                    }),
            ])
            ->actions([
                Action::make('administrar')
                    ->label('Ver & Administrar')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->url(fn (Ubicacion $record) => GestionarCarrito::getUrl(['carrito' => $record->id])),
            ]);
    }
}
