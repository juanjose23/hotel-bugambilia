<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\AuditoriaRestauranteResource;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Filament\Resources\Restaurante\AuditoriaRestauranteResource\Pages\ListAuditoriaRestaurantes;
use App\Repository\Models\Restaurante\AuditoriaRestaurante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AuditoriaRestauranteResource extends Resource
{
    protected static ?string $model = AuditoriaRestaurante::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?string $slug = 'restaurante/auditoria';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Auditoría POS';

    protected static ?string $modelLabel = 'Auditoría';

    protected static ?string $pluralModelLabel = 'Auditoría de Operaciones';

    protected static ?int $navigationSort = 14;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Fecha / Hora')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('usuario.name')->label('Usuario / Mesero')->placeholder('Sistema')->searchable(),
                TextColumn::make('accion')->label('Acción')->badge()->color('primary')->searchable(),
                TextColumn::make('mesa.nombre')->label('Mesa')->placeholder('—')->searchable(),
                TextColumn::make('pedido.codigo')->label('Pedido #')->placeholder('—')->searchable(),
                TextColumn::make('ip')->label('IP')->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('accion')
                    ->options(AccionAuditoriaRestaurante::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditoriaRestaurantes::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo()
            && parent::shouldRegisterNavigation();
    }

    public static function canViewAny(): bool
    {
        return app(VerificarRestauranteActivo::class)->estaActivo()
            && parent::canViewAny();
    }
}
