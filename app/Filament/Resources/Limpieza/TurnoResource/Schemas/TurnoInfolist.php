<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class TurnoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal del Turno')
                    ->icon(Heroicon::CheckBadge)
                    ->description('Datos generales e identificación del turno de limpieza.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre del Turno')
                            ->placeholder('-')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (EstadoGeneral|bool|int $state): string => match (true) {
                                $state instanceof EstadoGeneral => $state->getColor(),
                                (bool) $state => 'success',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (EstadoGeneral|bool|int $state): string => match (true) {
                                $state instanceof EstadoGeneral => $state->getLabel(),
                                (bool) $state => 'Activo',
                                default => 'Inactivo',
                            }),

                        TextEntry::make('es_lavanderia')
                            ->label('Área / Tipo')
                            ->badge()
                            ->color(fn (bool|int $state): string => (bool) $state ? 'primary' : 'gray')
                            ->formatStateUsing(fn (bool|int $state): string => (bool) $state ? 'Lavandería Dedicada' : 'Limpieza General'),

                        TextEntry::make('hora_inicio')
                            ->label('Hora de Inicio')
                            ->placeholder('-')
                            ->icon(Heroicon::Clock)
                            ->weight(FontWeight::Medium),

                        TextEntry::make('hora_fin')
                            ->label('Hora de Fin')
                            ->placeholder('-')
                            ->icon(Heroicon::Clock)
                            ->weight(FontWeight::Medium),
                    ]),

                Section::make('Equipo Asignado')
                    ->icon(Heroicon::Users)
                    ->description('Colaboradores responsables de la ejecución durante este turno.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->schema([
                        TextEntry::make('lider')
                            ->label('Líder de Turno')
                            ->state(fn (Turno $record) => $record->lider?->persona ? ObtenerNombrePersona::desde($record->lider->persona) : 'Sin asignar')
                            ->icon(Heroicon::User)
                            ->placeholder('Sin asignar')
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('apoyo')
                            ->label('Colaborador de Apoyo')
                            ->state(fn (Turno $record) => $record->apoyo?->persona ? ObtenerNombrePersona::desde($record->apoyo->persona) : 'Sin asignar')
                            ->icon(Heroicon::UserGroup)
                            ->placeholder('Sin asignar')
                            ->weight(FontWeight::Medium),
                    ]),

                Section::make('Recursos de Limpieza')
                    ->icon(Heroicon::ShoppingBag)
                    ->description('Carritos y bodegas de inventario vinculados a este turno.')
                    ->schema([
                        TextEntry::make('carritos')
                            ->hiddenLabel()
                            ->badge()
                            ->color('gray')
                            ->state(fn (Turno $record) => $record->carritos->pluck('nombre')->toArray() ?: null)
                            ->placeholder('Ningún carrito o bodega de limpieza vinculado a este turno.')
                            ->icon(Heroicon::Cube),
                    ]),

                Section::make('Metadatos')
                    ->icon(Heroicon::InformationCircle)
                    ->description('Fechas de creación y actualizaciones del registro.')
                    ->collapsible()
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}
