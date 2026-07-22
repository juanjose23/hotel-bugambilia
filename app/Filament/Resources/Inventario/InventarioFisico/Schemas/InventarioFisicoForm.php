<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Qalainau\UniverSheet\SpreadsheetField;

class InventarioFisicoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metadatos de Toma Física')
                    ->description('Registre la fecha y observaciones para la conciliación de inventario.')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('INF-YYYYMMDD-XXX')
                            ->prefixIcon(Heroicon::QrCode),

                        DatePicker::make('fecha_toma')
                            ->label('Fecha de Toma')
                            ->required()
                            ->default(now())
                            ->disabled(fn ($record) => $record?->estado === 'procesado')
                            ->prefixIcon(Heroicon::Calendar),

                        TextInput::make('creador_nombre')
                            ->label('Responsable')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(auth()->user()?->name)
                            ->placeholder(auth()->user()?->name)
                            ->prefixIcon(Heroicon::User),

                        Textarea::make('observaciones')
                            ->label('Observaciones / Notas')
                            ->nullable()
                            ->disabled(fn ($record) => $record?->estado === 'procesado')
                            ->columnSpanFull()
                            ->placeholder('Ingrese observaciones de esta toma de inventario físico...'),
                    ]),

                Section::make('Hoja de Conciliación (Físico vs Sistema)')
                    ->description('Utilice la hoja de cálculo interactiva para registrar las cantidades reales contadas físicamente.')
                    ->columnSpanFull()
                    ->schema([
                        (class_exists(SpreadsheetField::class)
                            ? SpreadsheetField::make('datos_hoja')
                                ->label('Hoja de Conciliación')
                                ->height('600px')
                                ->minHeight('400px')
                                ->columnSpanFull()
                                ->disabled(fn ($record) => $record?->estado === 'procesado')
                                ->ribbonType('collapsed')
                                ->showToolbar(fn ($record) => $record?->estado !== 'procesado')
                                ->showFormulaBar(fn ($record) => $record?->estado !== 'procesado')
                                ->showContextMenu(fn ($record) => $record?->estado !== 'procesado')
                            : Textarea::make('datos_hoja')
                                ->label('Hoja de Conciliación (Formato JSON)')
                                ->helperText('⚠️ Instale el paquete "qalainau/filament-univer-sheet" y ejecute "php artisan filament:assets" para habilitar la hoja de cálculo interactiva de Univer Sheet.')
                                ->rows(15)
                                ->columnSpanFull()
                                ->disabled(fn ($record) => $record?->estado === 'procesado')
                                ->afterStateHydrated(function ($component, $state) {
                                    if (is_array($state)) {
                                        $component->state(json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    }
                                })
                                ->dehydrateStateUsing(function ($state) {
                                    if (is_string($state)) {
                                        return json_decode($state, true);
                                    }

                                    return $state;
                                })),
                    ]),
            ]);
    }
}
