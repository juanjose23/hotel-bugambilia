<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReporte;

use App\Filament\Resources\Auditoria\AuditoriaReporte\Pages\ListAuditoriaReportes;
use App\Filament\Resources\Auditoria\AuditoriaReporte\Pages\ViewAuditoriaReporte;
use App\Filament\Resources\Auditoria\AuditoriaReporte\Schemas\AuditoriaReporteInfolist;
use App\Filament\Resources\Auditoria\AuditoriaReporte\Tables\AuditoriaReporteTable;
use App\Models\Audits\AuditoriaReporte;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class AuditoriaReporteResource extends Resource
{
    protected static ?string $model = AuditoriaReporte::class;

    protected static ?string $modelLabel = 'Auditoría de Reportes';

    protected static ?string $pluralModelLabel = 'Auditoría de Reportes';
    protected static string|UnitEnum|null $navigationGroup = 'Auditoria';

    protected static ?string $navigationLabel = 'Auditoría de Reportes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    public static function infolist(Schema $schema): Schema
    {
        return AuditoriaReporteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditoriaReporteTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditoriaReportes::route('/'),
            'view' => ViewAuditoriaReporte::route('/{record}'),
        ];
    }
}
