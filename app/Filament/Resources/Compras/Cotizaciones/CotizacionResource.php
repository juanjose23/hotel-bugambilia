<?php

namespace App\Filament\Resources\Compras\Cotizaciones;

use App\Filament\Resources\Compras\Cotizaciones\Pages\ComparativaCotizaciones;
use App\Filament\Resources\Compras\Cotizaciones\Pages\CreateCotizacion;
use App\Filament\Resources\Compras\Cotizaciones\Pages\EditCotizacion;
use App\Filament\Resources\Compras\Cotizaciones\Pages\ListCotizaciones;
use App\Filament\Resources\Compras\Cotizaciones\Pages\ViewCotizacion;
use App\Filament\Resources\Compras\Cotizaciones\Schemas\CotizacionForm;
use App\Filament\Resources\Compras\Cotizaciones\Schemas\CotizacionInfolist;
use App\Filament\Resources\Compras\Cotizaciones\Tables\CotizacionTable;
use App\Repository\Models\Compras\Cotizacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CotizacionResource extends Resource
{
    protected static ?string $model = Cotizacion::class;

    protected static ?string $slug = 'compras/cotizaciones';

    protected static ?string $modelLabel = 'Cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Compras & Proveedores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::ClipboardDocumentCheck;
    }

    public static function form(Schema $schema): Schema
    {
        return CotizacionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CotizacionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CotizacionTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'solicitud.ordenesCompra',
                'proveedor.persona.personaJuridica',
                'proveedor.persona.personaNatural',
                'creadaPor',
                'elegidaPor',
                'ordenCompra',
                'moneda',
                'items.producto',
                'items.variante',
            ])
            ->withCount([
                'items as items_elegidos_count' => fn ($q) => $q->where('es_elegido', true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCotizaciones::route('/'),
            'create' => CreateCotizacion::route('/create'),
            'comparativa' => ComparativaCotizaciones::route('/comparativa'),
            'view' => ViewCotizacion::route('/{record}'),
            'edit' => EditCotizacion::route('/{record}/edit'),
        ];
    }
}
