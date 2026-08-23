<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\SolicitudLimpiezaResource;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Pages\CreateSolicitudLimpieza;
use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Pages\EditSolicitudLimpieza;
use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Pages\ListSolicitudesLimpieza;
use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Schemas\SolicitudLimpiezaForm;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class SolicitudLimpiezaResource extends Resource
{
    protected static ?string $model = SolicitudLimpieza::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza & Lavandería';

    protected static ?string $navigationLabel = 'Solicitudes de Limpieza';

    protected static ?string $modelLabel = 'Solicitud de Limpieza';

    protected static ?string $pluralModelLabel = 'Solicitudes de Limpieza';

    protected static ?string $slug = 'limpieza/solicitudes';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SolicitudLimpiezaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['limpiable', 'personal', 'creador']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('limpiable.nombre')
                    ->label('Ubicación / Área')
                    ->placeholder('Sin nombre')
                    ->sortable(),
                TextColumn::make('limpiable_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Repository\\Models\\Habitaciones\\Habitacion' => 'Habitación',
                        'App\\Repository\\Models\\Espacios\\Espacio' => 'Espacio / Mesa',
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'Habitacion') ? 'info' : 'warning'),
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'alta' => 'danger',
                        'normal' => 'warning',
                        'baja' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('personal.name')
                    ->label('Personal Asignado')
                    ->placeholder('Sin asignar'),
                EstadoBadgeColumn::make(EstadoLimpieza::class),
                TextColumn::make('creador.name')
                    ->label('Creada por')
                    ->placeholder('Sistema'),
                FechaStandardColumn::make('created_at', 'Creada')
                    ->toggleable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoLimpieza::class),
                SelectFilter::make('prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'normal' => 'Normal',
                        'baja' => 'Baja',
                    ]),
                SelectFilter::make('limpiable_type')
                    ->label('Tipo de Objeto')
                    ->options([
                        'App\\Repository\\Models\\Habitaciones\\Habitacion' => 'Habitación',
                        'App\\Repository\\Models\\Espacios\\Espacio' => 'Espacio / Mesa',
                    ]),
            ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListSolicitudesLimpieza::route('/'),
            'create' => CreateSolicitudLimpieza::route('/create'),
            'edit' => EditSolicitudLimpieza::route('/{record}/edit'),
        ];
    }
}
