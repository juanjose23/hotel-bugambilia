<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource;

use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

final class FacturaAutorizacionDgiResource extends Resource
{
    protected static ?string $model = FacturaAutorizacionDgi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Autorizaciones DGI';

    protected static ?string $modelLabel = 'Autorizacion DGI';

    protected static ?string $pluralModelLabel = 'Autorizaciones DGI';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'facturacion/autorizaciones-dgi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Autorizacion y rango')
                ->columns(3)
                ->schema([
                    Select::make('factura_serie_id')->relationship('serie', 'codigo')->searchable()->preload()->required(),
                    TextInput::make('numero_autorizacion')->required()->maxLength(80)->unique(ignoreRecord: true)->columnSpan(2),
                    DatePicker::make('fecha_autorizacion')->required()->native(false),
                    DatePicker::make('vence_at')->label('Vence')->native(false),
                    Toggle::make('activa')->default(true),
                    TextInput::make('rango_desde')->integer()->minValue(1)->required(),
                    TextInput::make('rango_hasta')->integer()->minValue(1)->required(),
                ]),
            Section::make('Datos del emisor')
                ->columns(2)
                ->schema([
                    TextInput::make('ruc_emisor')->label('RUC')->required()->maxLength(30),
                    TextInput::make('razon_social_emisor')->required()->maxLength(180),
                    TextInput::make('nombre_comercial_emisor')->maxLength(180),
                    TextInput::make('direccion_emisor')->maxLength(255),
                    Textarea::make('pie_imprenta_fiscal')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_autorizacion', 'desc')
            ->columns([
                TextColumn::make('serie.codigo')->label('Serie')->sortable()->searchable(),
                TextColumn::make('numero_autorizacion')->label('Autorizacion')->searchable()->copyable(),
                TextColumn::make('ruc_emisor')->label('RUC')->searchable(),
                TextColumn::make('rango_desde')->label('Desde')->numeric(),
                TextColumn::make('rango_hasta')->label('Hasta')->numeric(),
                TextColumn::make('vence_at')->date()->label('Vence')->sortable(),
                IconColumn::make('activa')->boolean()->label('Activa'),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaAutorizacionesDgi::route('/'),
            'create' => Pages\CreateFacturaAutorizacionDgi::route('/create'),
            'edit' => Pages\EditFacturaAutorizacionDgi::route('/{record}/edit'),
        ];
    }
}
