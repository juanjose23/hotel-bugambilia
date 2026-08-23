<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CargoFacturacionResource;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Cuentas\CargoFacturacion;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class CargoFacturacionResource extends Resource
{
    protected static ?string $model = CargoFacturacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Cargos de Facturación';

    protected static ?string $modelLabel = 'Cargo';

    protected static ?string $pluralModelLabel = 'Cargos de Facturación';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'cargos-facturacion';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos del Cargo')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(2),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoCargo::class)
                            ->required(),
                        Select::make('modo_calculo')
                            ->label('Modo de Cálculo')
                            ->options(ModoCargo::class)
                            ->required(),
                        TextInput::make('valor')
                            ->label('Valor (% / Monto)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Select::make('base_calculo')
                            ->label('Base de Cálculo')
                            ->options(BaseCalculo::class)
                            ->required(),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                        Toggle::make('obligatorio')
                            ->label('Obligatorio')
                            ->default(false),
                        Toggle::make('permite_modificacion')
                            ->label('Permite Modificación')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('orden')
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('tipo')->label('Tipo')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof TipoCargo ? $state->getLabel() : '')
                    ->color(fn (mixed $state): string => $state instanceof TipoCargo ? $state->getColor() : 'gray'),
                TextColumn::make('modo_calculo')->label('Modo')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof ModoCargo ? $state->getLabel() : ''),
                TextColumn::make('valor')->label('Valor')->sortable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('obligatorio')->label('Obligatorio')->boolean(),
                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->filters([
                SelectFilter::make('tipo')->options(TipoCargo::class),
                FiltroEstado::make(EstadoGeneral::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCargosFacturacion::route('/'),
            'create' => Pages\CreateCargoFacturacion::route('/create'),
            'edit' => Pages\EditCargoFacturacion::route('/{record}/edit'),
        ];
    }
}
