<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ColaboradorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getSchema());
    }

    /** @return array<mixed> */
    public static function getSchema(): array
    {
        return [
            Section::make('Perfil del colaborador')
                ->schema(components: [
                    Grid::make(3)->schema(components: [
                        ImageEntry::make('colaborador.imagen.url')
                            ->label('Foto')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->primer_nombre.' '.$record->personaNatural?->primer_apellido).'&size=512&background=711c37&color=fff'),
                        TextEntry::make('nombre_completo')
                            ->label('Nombre completo')
                            ->getStateUsing(fn ($record) => trim($record->primer_nombre.' '.($record->segundo_nombre ?? '').' '.($record->personaNatural->primer_apellido ?? '').' '.($record->personaNatural->segundo_apellido ?? ''))),
                        TextEntry::make('colaborador.display_name')
                            ->label('Código interno')
                            ->badge()
                            ->icon(Heroicon::Identification),
                    ]),
                ])
                ->columns(1),

            Section::make('Información de Identidad')
                ->icon(Heroicon::FingerPrint)
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('personaNatural.numero_identificacion')
                            ->label('Documento Identidad')
                            ->icon(Heroicon::CreditCard)
                            ->suffix(fn ($record) => $record->personaNatural?->tipo_identificacion ? ' ('.$record->personaNatural->tipo_identificacion.')' : ''),

                        TextEntry::make('personaNatural.fecha_nacimiento')
                            ->label('Nacimiento')
                            ->icon(Heroicon::Calendar)
                            ->date('d/m/Y'),

                        TextEntry::make('personaNatural.sexo')
                            ->label('Género')
                            ->icon(Heroicon::Users)
                            ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculino' : 'Femenino'),

                        TextEntry::make('direccion')
                            ->label('Dirección de Domicilio')
                            ->icon(Heroicon::MapPin)
                            ->columnSpan(2),
                    ]),
                ]),

            Tabs::make('Expediente del Colaborador')
                ->tabs([
                    Tab::make('Historial de Cargos')
                        ->icon(Heroicon::RectangleStack)
                        ->schema([
                            RepeatableEntry::make('colaborador.cargosHistorial')
                                ->hiddenLabel()
                                ->grid()
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            TextEntry::make('cargo.nombre')
                                                ->label('Posición')
                                                ->color('primary'),
                                            TextEntry::make('departamento.nombre')
                                                ->label('Departamento'),
                                            TextEntry::make('periodo')
                                                ->label('Periodo')
                                                ->getStateUsing(fn ($record) => $record->fecha_inicio->format('M Y').' - '.($record->fecha_fin ? $record->fecha_fin->format('M Y') : 'ACTUAL'))
                                                ->icon(Heroicon::Clock),
                                        ])->compact(),
                                ]),
                        ]),

                    Tab::make('Información de Salud')
                        ->icon(Heroicon::Heart)
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('colaborador.datosMedicos.tipo_sangre')
                                    ->label('Tipo de Sangre')
                                    ->badge()
                                    ->color('danger')
                                    ->icon(Heroicon::Beaker)
                                    ->alignCenter(),

                                TextEntry::make('colaborador.datosMedicos.alergias')
                                    ->label('Alergias / Sensibilidades')
                                    ->placeholder('Ninguna detectada')
                                    ->icon(Heroicon::ExclamationTriangle)
                                    ->markdown(),

                                TextEntry::make('colaborador.datosMedicos.enfermedades_cronicas')
                                    ->label('Condiciones Médicas / Crónicas')
                                    ->placeholder('Ninguna registrada')
                                    ->icon(Heroicon::Hashtag)
                                    ->markdown(),
                            ]),
                        ]),

                    Tab::make('Historial Salarial')
                        ->icon(Heroicon::CurrencyDollar)
                        ->schema([
                            RepeatableEntry::make('colaborador.salarios')
                                ->hiddenLabel()
                                ->grid(3)
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            TextEntry::make('salario')
                                                ->money('NIO')
                                                ->color('success'),
                                            TextEntry::make('fecha_inicio')
                                                ->label('Desde')
                                                ->date(),
                                        ])->compact(),
                                ]),
                        ]),

                    Tab::make('Contactos')
                        ->icon(Heroicon::Users)
                        ->schema([
                            RepeatableEntry::make('colaborador.contactosEmergencia')
                                ->hiddenLabel()
                                ->grid(3)
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            TextEntry::make('nombre'),
                                            TextEntry::make('parentesco')->color('gray'),
                                            TextEntry::make('telefono')->icon(Heroicon::Phone)->color('primary'),
                                        ])->compact(),
                                ]),
                        ]),

                    Tab::make('Documentos')
                        ->icon(Heroicon::FolderOpen)
                        ->schema([
                            RepeatableEntry::make('colaborador.documentos')
                                ->hiddenLabel()
                                ->grid(4)
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            TextEntry::make('tipo')
                                                ->hiddenLabel()
                                                ->icon(Heroicon::DocumentText)
                                                ->color('primary')
                                                ->url(fn ($record) => asset('storage/'.$record->archivo))
                                                ->openUrlInNewTab(),
                                        ])->compact(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
