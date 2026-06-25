<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\Limpieza\LimpiezaEjecucion;
use BackedEnum;
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
    use InteractsWithTable;

    protected string $view = 'filament.pages.limpieza.abastecer-carrito';

    protected static ?string $slug = 'limpieza/abastecer-carrito';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $navigationLabel = 'Administración de Carritos';

    protected static ?string $title = 'Administración de Carritos de Limpieza';

    protected static ?int $navigationSort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(Ubicacion::where('nombre', 'like', 'Carrito%'))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del Carrito')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción'),
                TextColumn::make('insumos_count')
                    ->label('Cantidad de Insumos')
                    ->state(fn (Ubicacion $record) => Stock::where('ubicacion_id', $record->id)->where('cantidad', '>', 0)->count()),
                TextColumn::make('abastecido_por')
                    ->label('Último Abastecedor')
                    ->state(function (Ubicacion $record) {
                        $mov = MovimientoStock::with('creadoPor.persona')
                            ->where('ubicacion_destino_id', $record->id)
                            ->where('tipo', 'TRASLADO')
                            ->latest()
                            ->first();
                        if (! $mov || ! $mov->creadoPor) {
                            return 'N/A';
                        }
                        $p = $mov->creadoPor->persona;

                        return $p ? trim($p->primer_nombre.' '.($p->personaNatural->primer_apellido ?? '')) : $mov->creadoPor->name;
                    }),
                IconColumn::make('bloqueado')
                    ->label('En Uso')
                    ->state(fn (Ubicacion $record) => LimpiezaEjecucion::where('estado', EstadoLimpieza::EnProgreso)->where('carrito_id', $record->id)->exists())
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
                    ->action(function (array $data) {
                        Ubicacion::create([
                            'nombre' => $data['nombre'],
                            'descripcion' => $data['descripcion'],
                            'tipo' => 'almacen',
                            'estado' => 1,
                            'orden' => 10,
                        ]);
                        Notification::make()->title('Carrito Creado')->body('El nuevo carrito se ha registrado con éxito.')->success()->send();
                    }),
            ])
            ->actions([
                Action::make('ver_stock')
                    ->label('Ver Stock')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalWidth('5xl')
                    ->modalHeading(fn (Ubicacion $record) => "Stock e Historial: {$record->nombre}")
                    ->modalContent(fn (Ubicacion $record) => view('filament.pages.limpieza.carrito-stock-modal', ['record' => $record])),

                Action::make('administrar')
                    ->label('Administrar')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->url(fn (Ubicacion $record) => GestionarCarrito::getUrl(['carrito' => $record->id])),
            ]);
    }
}
