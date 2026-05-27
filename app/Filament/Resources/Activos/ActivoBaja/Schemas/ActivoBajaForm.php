<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja\Schemas;

use App\Enums\Activos\TipoBaja;
use App\Models\Activos\Activo;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ActivoBajaForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Acta de Baja de Activo Fijo')
                ->description('Registro legal y técnico para desincorporación definitiva del bien.')
                ->schema([
                    TextInput::make('codigo')
                        ->label('Número de Folio')
                        ->disabled()
                        ->placeholder('Se genera automáticamente al procesar'),

                    Select::make('activo_id')
                        ->label('Activo Fijo a Retirar')
                        ->relationship('activo', 'nombre_descriptivo')
                        ->options(Activo::where('estado', '!=', 3)->pluck('nombre_descriptivo', 'id'))
                        ->required()
                        ->searchable(),

                    DatePicker::make('fecha_baja')
                        ->label('Fecha Efectiva de Baja')
                        ->required()
                        ->default(now()),

                    Select::make('motivo_tipo')
                        ->label('Causa de Desincorporación')
                        ->options(TipoBaja::class)
                        ->required(),

                    Textarea::make('motivo_detalle')
                        ->label('Justificación Técnica y Detalles de Estado')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Autorizaciones y Recuperación')
                ->description('Firmas administrativas y valor remanente del bien.')
                ->schema([
                    TextInput::make('valor_residual')
                        ->label('Valor de Recuperación / Residuo')
                        ->numeric()
                        ->prefixIcon(Heroicon::CurrencyDollar)
                        ->placeholder('0.00'),

                    Select::make('aprobado_por_id')
                        ->label('Aprobado Por (Administrador)')
                        ->relationship('aprobadoPor', 'name')
                        ->options(User::pluck('name', 'id'))
                        ->searchable(),

                    Select::make('creado_por_id')
                        ->label('Registrado Por')
                        ->relationship('creadoPor', 'name')
                        ->options(User::pluck('name', 'id'))
                        ->default(fn () => auth()->id())
                        ->required()
                        ->disabled(),

                    TextInput::make('documento_soporte')
                        ->label('Documento Digital de Soporte')
                        ->placeholder('Ruta al acta escaneada o archivo PDF'),
                ])
                ->columns(2),
        ]);
    }
}
