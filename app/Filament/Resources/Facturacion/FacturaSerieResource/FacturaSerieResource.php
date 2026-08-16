<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaSerieResource;

use App\Repository\Models\Facturacion\FacturaSerie;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class FacturaSerieResource extends Resource
{
    protected static ?string $model = FacturaSerie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración & Auditoría';

    protected static ?string $navigationLabel = 'Series fiscales';

    protected static ?string $modelLabel = 'Serie fiscal';

    protected static ?string $pluralModelLabel = 'Series fiscales';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'facturacion/series';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Configuracion de la serie')
                ->columns(3)
                ->schema([
                    TextInput::make('codigo')->label('Serie')->required()->maxLength(20)->unique(ignoreRecord: true),
                    TextInput::make('nombre')->label('Nombre')->required()->maxLength(120)->columnSpan(2),
                    TextInput::make('sucursal_codigo')->label('Sucursal')->maxLength(30),
                    TextInput::make('caja_codigo')->label('Caja')->maxLength(30),
                    TextInput::make('siguiente_numero')->label('Siguiente folio')->integer()->minValue(1)->required(),
                    Toggle::make('activa')->label('Activa')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('codigo')
            ->columns([
                TextColumn::make('codigo')->label('Serie')->searchable()->sortable()->weight('bold'),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('sucursal_codigo')->label('Sucursal')->placeholder('-'),
                TextColumn::make('caja_codigo')->label('Caja')->placeholder('-'),
                TextColumn::make('siguiente_numero')->label('Siguiente')->numeric()->sortable(),
                IconColumn::make('activa')->boolean()->label('Activa'),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaSeries::route('/'),
            'create' => Pages\CreateFacturaSerie::route('/create'),
            'edit' => Pages\EditFacturaSerie::route('/{record}/edit'),
        ];
    }
}
