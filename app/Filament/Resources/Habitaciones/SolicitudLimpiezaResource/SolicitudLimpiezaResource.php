<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource;

use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages\CreateSolicitudLimpieza;
use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages\EditSolicitudLimpieza;
use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages\ListSolicitudLimpiezas;
use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages\ViewSolicitudLimpieza;
use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Schemas\SolicitudLimpiezaForm;
use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Tables\SolicitudLimpiezaTable;
use App\Models\Limpieza\SolicitudLimpieza;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SolicitudLimpiezaResource extends Resource
{
    protected static ?string $model = SolicitudLimpieza::class;

    protected static ?string $slug = 'habitaciones/solicitudes-limpieza';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $modelLabel = 'Solicitud de Limpieza';

    protected static ?string $pluralModelLabel = 'Solicitudes de Limpieza';

    protected static ?int $navigationSort = 4;

    /**
     * Configura el esquema del formulario.
     */
    public static function form(Schema $schema): Schema
    {
        return app(SolicitudLimpiezaForm::class)->configure($schema);
    }

    /**
     * Configura la tabla.
     */
    public static function table(Table $table): Table
    {
        return app(SolicitudLimpiezaTable::class)->configure($table);
    }

    /**
     * Define las páginas y rutas asociadas al recurso.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListSolicitudLimpiezas::route('/'),
            'create' => CreateSolicitudLimpieza::route('/create'),
            'view' => ViewSolicitudLimpieza::route('/{record}'),
            'edit' => EditSolicitudLimpieza::route('/{record}/edit'),
        ];
    }
}
