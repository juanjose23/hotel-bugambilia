<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\Audits;

use App\Filament\Resources\Auditoria\Audits\Pages\ListAudits;
use App\Filament\Resources\Auditoria\Audits\Pages\ViewAudit;
use App\Filament\Resources\Auditoria\Audits\Schemas\AuditInfolist;
use App\Filament\Resources\Auditoria\Audits\Tables\AuditsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use OwenIt\Auditing\Models\Audit;
use UnitEnum;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static ?string $navigationLabel = 'Auditoria';

    protected static ?string $pluralModelLabel = 'Auditoría de cambios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración & Auditoría';

    public static function infolist(Schema $schema): Schema
    {
        return AuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
            'view' => ViewAudit::route('/{record}'),
        ];
    }
}
