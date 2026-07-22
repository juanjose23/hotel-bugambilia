<?php

namespace App\Filament\Resources\Compras\Solicitudes\Schemas;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Compras\Solicitud;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SolicitudInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitud de Compra')
                    ->description('Datos generales de la solicitud')
                    ->icon(Heroicon::DocumentArrowUp)
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Código')
                            ->icon(Heroicon::QrCode)
                            ->badge()
                            ->color('primary')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('colaborador.codigo')
                            ->label('Colaborador')
                            ->icon(Heroicon::User)
                            ->formatStateUsing(fn ($state, ?Solicitud $record) => ($record && $record->colaborador) ? ($record->colaborador->codigo.' - '.($record->colaborador->persona ? $record->colaborador->persona->primer_nombre : '')) : ''),

                        TextEntry::make('departamentoSolicitante.nombre')
                            ->label('Departamento')
                            ->icon(Heroicon::BuildingOffice2)
                            ->placeholder('—'),

                        TextEntry::make('fecha_solicitud')
                            ->label('Fecha Solicitud')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('fecha_necesita')
                            ->label('Fecha Necesita')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Clock)
                            ->placeholder('No definida'),

                        TextEntry::make('motivo')
                            ->label('Motivo')
                            ->placeholder('Sin motivo')
                            ->markdown()
                            ->columnSpanFull(),

                        TextEntry::make('notas')
                            ->label('Nota del área de compras')
                            ->placeholder('Sin notas')
                            ->markdown()
                            ->visible(fn ($record): bool => $record?->estado === EstadoSolicitud::Cancelada)
                            ->columnSpanFull(),
                    ]),

                Section::make('Productos Solicitados')
                    ->description('Detalle de los productos incluidos en la solicitud')
                    ->icon(Heroicon::ShoppingBag)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto'),

                                TextEntry::make('productoVariante.codigo')
                                    ->label('Variante')
                                    ->placeholder('—'),

                                TextEntry::make('cantidad_solicitada')
                                    ->label('Cantidad Solicitada'),

                                TextEntry::make('cantidad_aprobada')
                                    ->label('Cantidad Aprobada')
                                    ->placeholder('Pendiente'),

                                TextEntry::make('observaciones')
                                    ->label('Observaciones')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Auditoría')
                    ->description('Fechas de registro en el sistema')
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}
