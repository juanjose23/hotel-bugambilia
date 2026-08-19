<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PasarelaPagoResource;

use App\Repository\Models\Facturacion\PasarelaPago;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
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

final class PasarelaPagoResource extends Resource
{
    protected static ?string $model = PasarelaPago::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Pasarelas de pago';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'facturacion/pasarelas';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Proveedor')
                ->columns(2)
                ->schema([
                    TextInput::make('codigo')->required()->maxLength(50)->unique(ignoreRecord: true),
                    TextInput::make('nombre')->required()->maxLength(120),
                    Toggle::make('activa')->default(true),
                    Toggle::make('modo_prueba')->label('Modo prueba')->default(true),
                    KeyValue::make('configuracion')->label('Configuracion segura')->columnSpanFull(),
                    Select::make('proveedor')
                        ->options(['stripe' => 'Stripe', 'paypal' => 'PayPal'])
                        ->nullable(),
                    Select::make('gestion')
                        ->options(['sistema' => 'Sistema', 'manual' => 'Manual'])
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nombre')
            ->columns([
                TextColumn::make('codigo')->searchable()->sortable()->weight('bold'),
                TextColumn::make('nombre')->searchable()->sortable(),
                IconColumn::make('activa')->boolean(),
                IconColumn::make('modo_prueba')->boolean()->label('Prueba'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPasarelasPago::route('/'),
            'create' => Pages\CreatePasarelaPago::route('/create'),
            'edit' => Pages\EditPasarelaPago::route('/{record}/edit'),
        ];
    }
}
