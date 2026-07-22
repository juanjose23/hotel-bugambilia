<?php

namespace App\Filament\Resources\Compras\Proveedors\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProveedorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor')
                    ->description('Datos generales del proveedor')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(3)
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
                            ->badge()
                            ->color(fn ($state) => EstadoGeneral::colorFor($state ?? '') ?? 'gray')
                            ->formatStateUsing(fn ($state): string => EstadoGeneral::labelFor($state)),

                        TextEntry::make('persona.primer_nombre')
                            ->label('Nombre / Razón Social')
                            ->icon(Heroicon::User)
                            ->columnSpan(2),

                        TextEntry::make('persona.personaNatural.primer_apellido')
                            ->label('Apellidos')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'natural'),

                        TextEntry::make('persona.personaJuridica.razon_social')
                            ->label('Razón Social')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'juridica')
                            ->columnSpan(2),

                        TextEntry::make('persona.tipo_persona')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn ($state): string => $state === 'natural' ? 'info' : 'warning')
                            ->formatStateUsing(fn ($state): string => $state === 'natural' ? 'Persona Natural' : 'Persona Jurídica'),

                        TextEntry::make('persona.personaNatural.tipo_identificacion')
                            ->label('Tipo Identificación')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'natural'),

                        TextEntry::make('persona.personaNatural.numero_identificacion')
                            ->label('Número Identificación')
                            ->placeholder('—')
                            ->copyable()
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'natural'),

                        TextEntry::make('persona.personaJuridica.tipo_identificacion')
                            ->label('Tipo Identificación Fiscal')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'juridica'),

                        TextEntry::make('persona.personaJuridica.numero_identificacion')
                            ->label('Número Identificación Fiscal')
                            ->placeholder('—')
                            ->copyable()
                            ->visible(fn ($record): bool => $record?->persona?->tipo_persona === 'juridica'),

                        TextEntry::make('persona.pais.nombre')
                            ->label('País')
                            ->placeholder('—')
                            ->icon(Heroicon::GlobeAmericas),

                        TextEntry::make('persona.telefono')
                            ->label('Teléfono')
                            ->placeholder('—')
                            ->icon(Heroicon::Phone),

                        TextEntry::make('persona.direccion')
                            ->label('Dirección')
                            ->placeholder('—')
                            ->icon(Heroicon::MapPin)
                            ->columnSpanFull(),
                    ]),

                Section::make('Información Comercial')
                    ->description('Condiciones y datos comerciales')
                    ->icon(Heroicon::ShoppingCart)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('tipoProveedor.nombre')
                            ->label('Tipo de Proveedor')
                            ->placeholder('—')
                            ->icon(Heroicon::GlobeAlt)
                            ->badge(),

                        TextEntry::make('direccion_fiscal')
                            ->label('Dirección Fiscal')
                            ->placeholder('—')
                            ->icon(Heroicon::MapPin)
                            ->columnSpanFull(),

                        TextEntry::make('contactoPrincipal.nombre')
                            ->label('Contacto')
                            ->placeholder('—')
                            ->icon(Heroicon::User),

                        TextEntry::make('contactoPrincipal.telefono')
                            ->label('Teléfono Contacto')
                            ->placeholder('—')
                            ->icon(Heroicon::Phone),

                        TextEntry::make('contactoPrincipal.email')
                            ->label('Email Contacto')
                            ->placeholder('—')
                            ->icon(Heroicon::Envelope),

                        TextEntry::make('notas')
                            ->label('Notas')
                            ->placeholder('Sin notas')
                            ->markdown()
                            ->columnSpanFull(),
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
