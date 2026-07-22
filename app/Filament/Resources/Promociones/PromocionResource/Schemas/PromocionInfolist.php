<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Promociones\Promocion;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PromocionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal de la Promoción')
                    ->icon(Heroicon::Ticket)
                    ->description('Datos de identificación, estado y visibilidad de la oferta.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Código')
                            ->placeholder('-')
                            ->icon(Heroicon::Hashtag)
                            ->weight('bold'),

                        TextEntry::make('nombre')
                            ->label('Nombre del Paquete Promocional')
                            ->placeholder('-')
                            ->icon(Heroicon::Sparkles)
                            ->weight('bold'),

                        TextEntry::make('tipo.nombre')
                            ->label('Tipo de Promoción')
                            ->placeholder('-')
                            ->icon(Heroicon::Tag),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state): ?string => is_string($color = EstadoGeneral::colorFor($state)) ? $color : null)
                            ->formatStateUsing(fn ($state): string => EstadoGeneral::labelFor($state)),

                        TextEntry::make('web')
                            ->label('Visible en Portal Web')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Publicada en Web' : 'Interna / Oculta'),

                        TextEntry::make('orden')
                            ->label('Orden de Prioridad')
                            ->placeholder('-')
                            ->icon(Heroicon::Bars3CenterLeft),
                    ]),

                Section::make('Precio del Paquete y Descuentos')
                    ->icon(Heroicon::Banknotes)
                    ->description('Costo global del paquete y beneficios económicos aplicados.')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('precio_paquete')
                            ->label('Precio Base del Paquete')
                            ->icon(Heroicon::Banknotes)
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => $state ? "C$ {$state}" : 'Por Calcular'),

                        TextEntry::make('precio_final')
                            ->label('Precio Final Oferta')
                            ->icon(Heroicon::CheckBadge)
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn ($state) => $state !== null ? "C$ {$state}" : 'Consultar'),

                        TextEntry::make('descuento_porcentaje')
                            ->label('Descuento Porcentual')
                            ->icon(Heroicon::PercentBadge)
                            ->badge()
                            ->color('emerald')
                            ->formatStateUsing(fn ($state) => $state ? "{$state}% OFF" : '-'),

                        TextEntry::make('descuento_monto')
                            ->label('Descuento Fijo (Monto)')
                            ->icon(Heroicon::CurrencyDollar)
                            ->badge()
                            ->color('amber')
                            ->formatStateUsing(fn ($state) => $state ? "C$ {$state}" : '-'),
                    ]),

                Section::make('Vigencia de la Oferta')
                    ->icon(Heroicon::Calendar)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->icon(Heroicon::Calendar)
                            ->date(),

                        TextEntry::make('fecha_fin')
                            ->label('Fecha de Vencimiento')
                            ->icon(Heroicon::Clock)
                            ->date(),
                    ]),

                Section::make('Detalles y Galería')
                    ->icon(Heroicon::DocumentText)
                    ->description('Descripción completa, condiciones y elementos visuales.')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('descripcion')
                            ->label('Descripción Comercial')
                            ->placeholder('Sin descripción.')
                            ->columnSpanFull(),

                        TextEntry::make('condiciones')
                            ->label('Términos, Restricciones y Condiciones')
                            ->placeholder('Sin condiciones especificadas.')
                            ->columnSpanFull(),

                        ImageEntry::make('imagenes.url')
                            ->label('Galería de Promoción')
                            ->disk('public')
                            ->visibility('public')
                            ->placeholder('Sin imágenes registradas.')
                            ->columnSpanFull(),

                        TextEntry::make('deleted_at')
                            ->label('Fecha de Eliminación')
                            ->dateTime()
                            ->visible(fn (Promocion $record): bool => $record->trashed())
                            ->columnSpanFull(),
                    ]),

                Section::make('Auditoría del Sistema')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsed()
                    ->schema([
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}
