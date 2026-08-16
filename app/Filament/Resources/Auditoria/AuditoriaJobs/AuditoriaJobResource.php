<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs;

use App\Filament\Resources\Auditoria\AuditoriaJobs\Pages\ListAuditoriaJobs;
use App\Filament\Resources\Auditoria\AuditoriaJobs\Pages\ViewAuditoriaJob;
use App\Filament\Resources\Auditoria\AuditoriaJobs\Schemas\AuditoriaJobInfolist;
use App\Filament\Resources\Auditoria\AuditoriaJobs\Tables\AuditoriaJobsTable;
use App\Repository\Models\Audits\AuditoriaJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AuditoriaJobResource extends Resource
{
    protected static ?string $model = AuditoriaJob::class;

    protected static ?string $navigationLabel = 'Jobs';

    protected static ?string $pluralModelLabel = 'Auditoría de Jobs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración & Auditoría';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return AuditoriaJobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditoriaJobsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditoriaJobs::route('/'),
            'view' => ViewAuditoriaJob::route('/{record}'),
        ];
    }
}
