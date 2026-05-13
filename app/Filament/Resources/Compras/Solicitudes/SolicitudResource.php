<?php

namespace App\Filament\Resources\Compras\Solicitudes;

use App\Filament\Resources\Compras\Solicitudes\Pages\CreateSolicitud;
use App\Filament\Resources\Compras\Solicitudes\Pages\EditSolicitud;
use App\Filament\Resources\Compras\Solicitudes\Pages\ListSolicitudes;
use App\Filament\Resources\Compras\Solicitudes\Pages\ViewSolicitud;
use App\Filament\Resources\Compras\Solicitudes\Schemas\SolicitudForm;
use App\Filament\Resources\Compras\Solicitudes\Schemas\SolicitudInfolist;
use App\Filament\Resources\Compras\Solicitudes\Tables\SolicitudTable;
use App\Models\Compras\Solicitud;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $slug = 'compras/solicitudes';

    public static function getModel(): string
    {
        return Solicitud::class;
    }

    protected static ?string $navigationLabel = 'Solicitudes';

    protected static ?string $modelLabel = 'Solicitud';

    protected static ?string $pluralModelLabel = 'Solicitudes de Compra';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentArrowUp;

    protected static ?string $recordTitleAttribute = 'codigo';

    public static function form(Schema $schema): Schema
    {
        return SolicitudForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SolicitudInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolicitudTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'colaborador.persona',
                'departamentoSolicitante',
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolicitudes::route('/'),
            'create' => CreateSolicitud::route('/create'),
            'view' => ViewSolicitud::route('/{record}'),
            'edit' => EditSolicitud::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
