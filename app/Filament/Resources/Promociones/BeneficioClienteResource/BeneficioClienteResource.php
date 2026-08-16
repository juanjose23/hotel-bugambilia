<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\BeneficioClienteResource;

use App\Filament\Resources\Promociones\BeneficioClienteResource\Pages\CreateBeneficioCliente;
use App\Filament\Resources\Promociones\BeneficioClienteResource\Pages\EditBeneficioCliente;
use App\Filament\Resources\Promociones\BeneficioClienteResource\Pages\ListBeneficiosCliente;
use App\Filament\Resources\Promociones\BeneficioClienteResource\Schemas\BeneficioClienteForm;
use App\Filament\Resources\Promociones\BeneficioClienteResource\Tables\BeneficioClienteTable;
use App\Repository\Models\Promociones\PromocionBeneficio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BeneficioClienteResource extends Resource
{
    protected static ?string $model = PromocionBeneficio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static ?string $slug = 'promociones/beneficios-cliente';

    protected static string|UnitEnum|null $navigationGroup = 'Servicios & Promociones';

    protected static ?string $navigationLabel = 'Beneficios de Cliente';

    protected static ?string $modelLabel = 'Beneficio de cliente';

    protected static ?string $pluralModelLabel = 'Beneficios de cliente';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return BeneficioClienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficioClienteTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeneficiosCliente::route('/'),
            'create' => CreateBeneficioCliente::route('/create'),
            'edit' => EditBeneficioCliente::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
