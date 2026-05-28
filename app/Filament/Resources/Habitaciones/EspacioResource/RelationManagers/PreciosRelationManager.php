<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Enums\Espacios\TipoPrecioEspacio;
use App\Enums\PrecioEstado;
use App\Models\Espacios\PrecioEspacio;
use App\Models\Monedas\Moneda;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PreciosRelationManager extends RelationManager
{
    protected static string $relationship = 'precios';

    protected static ?string $title = 'Precios de Espacio';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('moneda_id')
                    ->label('Moneda')
                    ->relationship('moneda', 'codigo')
                    ->required()
                    ->default(
                        fn (): ?int => Moneda::query()
                            ->where('codigo', 'NIO')
                            ->value('id')
                            ?? Moneda::query()
                                ->where('es_predeterminada', true)
                                ->value('id')
                    )
                    ->preload()
                    ->prefixIcon(Heroicon::Banknotes)
                    ->rules([$this->getUniquePrecioVigenteRule()]),

                Select::make('tipo_precio')
                    ->label('Concepto de Tarifa')
                    ->options(TipoPrecioEspacio::options())
                    ->default(TipoPrecioEspacio::Base->value)
                    ->required()
                    ->prefixIcon(Heroicon::Clock)
                    ->rules([$this->getUniquePrecioVigenteRule()]),

                TextInput::make('precio')
                    ->label('Precio')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefixIcon(Heroicon::CurrencyDollar)
                    ->rule(fn (callable $get): string => intval($get('estado')) === PrecioEstado::Vigente->value ? 'min:0.01' : 'min:0'),

                DatePicker::make('fecha_inicio')
                    ->label('Vigente desde')
                    ->required()
                    ->default(now())
                    ->prefixIcon(Heroicon::Calendar),

                DatePicker::make('fecha_fin')
                    ->label('Vigente hasta')
                    ->nullable()
                    ->afterOrEqual('fecha_inicio')
                    ->prefixIcon(Heroicon::Calendar)
                    ->helperText('Dejar vacío si es el precio vigente indefinidamente.'),

                Select::make('estado')
                    ->label('Estado')
                    ->options(PrecioEstado::options())
                    ->default(PrecioEstado::Vigente->value)
                    ->required()
                    ->prefixIcon(Heroicon::CheckCircle)
                    ->rules([$this->getUniquePrecioVigenteRule()])
                    ->live(),

                Toggle::make('es_oferta')
                    ->label('Es Oferta')
                    ->default(false)
                    ->inline(false)
                    ->rules([$this->getUniquePrecioVigenteRule()]),
            ]);
    }

    public function getUniquePrecioVigenteRule(): \Closure
    {
        return fn (callable $get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $component) {
            $estado = intval($get('estado'));
            $esOferta = (bool) $get('es_oferta');
            $monedaId = intval($get('moneda_id'));
            $tipoPrecio = $get('tipo_precio');

            if ($estado === 1 && ! $esOferta && $monedaId && $tipoPrecio) {
                $parentRecord = $this->getOwnerRecord();
                $record = $component->getRecord();

                $query = PrecioEspacio::where('espacio_id', $parentRecord->getKey())
                    ->where('moneda_id', $monedaId)
                    ->where('tipo_precio', $tipoPrecio)
                    ->where('estado', 1)
                    ->where('es_oferta', false);

                if ($record && $record->exists) {
                    $query->where('id', '!=', $record->getKey());
                }

                if ($query->exists()) {
                    $fail('Ya existe un precio o tarifa vigente activa para este espacio, esta moneda y este concepto. Desactive el precio anterior antes de guardar.');
                }
            }
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('moneda.codigo')
                    ->label('Moneda')
                    ->sortable(),

                TextColumn::make('tipo_precio')
                    ->label('Concepto')
                    ->badge()
                    ->color(fn ($state): string => $state instanceof TipoPrecioEspacio ? $state->getColor() : TipoPrecioEspacio::tryFrom($state)?->getColor() ?? 'gray')
                    ->formatStateUsing(fn ($state): string => $state instanceof TipoPrecioEspacio ? $state->getLabel() : TipoPrecioEspacio::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->money(fn ($record) => $record->moneda ? $record->moneda->codigo : 'USD')
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date()
                    ->placeholder('Permanente')
                    ->sortable(),

                IconColumn::make('es_oferta')
                    ->label('Es Oferta')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): string => PrecioEstado::colorFor($state))
                    ->formatStateUsing(fn ($state): string => PrecioEstado::labelFor($state))
                    ->sortable(),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions([
                CreateAction::make()->icon(Heroicon::Plus),
            ])
            ->actions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
