<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReportes;

use App\Filament\Resources\Auditoria\AuditoriaReportes\Pages\ListAuditoriaReportes;
use App\Filament\Resources\Auditoria\AuditoriaReportes\Pages\ViewAuditoriaReporte;
use App\Filament\Resources\Auditoria\AuditoriaReportes\Schemas\AuditoriaReporteInfolist;
use App\Filament\Resources\Auditoria\AuditoriaReportes\Tables\AuditoriaReportesTable;
use App\Repository\Models\Audits\AuditoriaReporte;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AuditoriaReporteResource extends Resource
{
    protected static ?string $model = AuditoriaReporte::class;

    protected static ?string $navigationLabel = 'Auditoria de Reportes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Auditoria';

    public static function infolist(Schema $schema): Schema
    {
        return AuditoriaReporteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditoriaReportesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditoriaReportes::route('/'),
            'view' => ViewAuditoriaReporte::route('/{record}'),
        ];
    }
}
