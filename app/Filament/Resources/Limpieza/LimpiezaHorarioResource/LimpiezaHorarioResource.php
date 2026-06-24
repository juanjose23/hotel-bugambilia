<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages\CreateLimpiezaHorario;
use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages\EditLimpiezaHorario;
use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages\ListLimpiezaHorarios;
use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Schemas\LimpiezaHorarioForm;
use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Tables\LimpiezaHorarioTable;
use App\Models\Limpieza\LimpiezaHorario;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LimpiezaHorarioResource extends Resource
{
    protected static ?string $model = LimpiezaHorario::class;

    protected static ?string $slug = 'limpieza/horarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $modelLabel = 'Horario Planificado';

    protected static ?string $pluralModelLabel = 'Horarios Planificados';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return LimpiezaHorarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LimpiezaHorarioTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListLimpiezaHorarios::route('/'),
            'create' => CreateLimpiezaHorario::route('/create'),
            'edit' => EditLimpiezaHorario::route('/{record}/edit'),
        ];
    }
}
