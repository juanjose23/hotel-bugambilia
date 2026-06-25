<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use App\Enums\HabitacionesEspacios\TipoPrecioEspacio;
use App\Enums\Shared\PrecioEstado;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Component;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasPreciosForm
{
    use HasStandardCrudActions;

    abstract protected function getPriceableModelClass(): string;

    abstract protected function getPriceableForeignKey(): string;

    protected function getPriceableForeignType(): ?string
    {
        return 'priceable_type';
    }

    protected function hasTipoPrecioField(): bool
    {
        return false;
    }

    protected function getDefaultMonedaId(): ?int
    {
        return null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('moneda_id')
                    ->label('Moneda')
                    ->relationship('moneda', 'codigo')
                    ->required()
                    ->default(fn (): ?int => $this->getDefaultMonedaId())
                    ->preload()
                    ->prefixIcon(Heroicon::Banknotes)
                    ->rules([$this->getUniquePrecioVigenteRule()]),

                ...$this->hasTipoPrecioField()
                    ? [
                        Select::make('tipo_precio')
                            ->label('Concepto de Tarifa')
                            ->options(TipoPrecioEspacio::options())
                            ->default(TipoPrecioEspacio::Base->value)
                            ->required()
                            ->prefixIcon(Heroicon::Clock)
                            ->rules([$this->getUniquePrecioVigenteRule()]),
                    ]
                    : [],

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

            $query = $this->buildUniquePrecioQuery($get, $component, $estado, $esOferta, $monedaId);

            if ($query === null) {
                return;
            }

            /** @var Model|null $record */
            $record = $component->getRecord();
            if ($record && $record->exists) {
                $query->where('id', '!=', $record->getKey());
            }

            if ($query->exists()) {
                $fail($this->getUniquePrecioErrorMessage());
            }
        };
    }

    /**
     * @return Builder<Model>|null
     */
    protected function buildUniquePrecioQuery(callable $get, mixed $component, int $estado, bool $esOferta, int $monedaId): ?Builder
    {
        if ($estado !== 1 || $esOferta || ! $monedaId) {
            return null;
        }

        $parentRecord = $this->getOwnerRecord();
        $modelClass = $this->getPriceableModelClass();

        $query = $modelClass::where($this->getPriceableForeignKey(), $parentRecord->getKey());

        $foreignType = $this->getPriceableForeignType();
        if ($foreignType !== null) {
            $query->where($foreignType, $parentRecord::class);
        }

        if ($this->hasTipoPrecioField()) {
            $tipoPrecio = $get('tipo_precio');
            if (! $tipoPrecio) {
                return null;
            }
            $query->where('tipo_precio', $tipoPrecio);
        }

        return $query->where('moneda_id', $monedaId)
            ->where('estado', 1)
            ->where('es_oferta', false);
    }

    protected function getUniquePrecioErrorMessage(): string
    {
        return 'Ya existe un precio vigente activo para este registro y esta moneda. Desactive el precio anterior antes de guardar.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getPriceTableColumns())
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions($this->getStandardHeaderActions())
            ->actions($this->getStandardRowActions());
    }

    /**
     * @return array<int, Column|ColumnGroup|Component>
     */
    protected function getPriceTableColumns(): array
    {
        return [
            TextColumn::make('moneda.codigo')
                ->label('Moneda')
                ->sortable(),

            ...$this->hasTipoPrecioField()
                ? [
                    TextColumn::make('tipo_precio')
                        ->label('Concepto')
                        ->badge()
                        ->color(fn ($state): string => $state instanceof TipoPrecioEspacio ? $state->getColor() : TipoPrecioEspacio::tryFrom($state)?->getColor() ?? 'gray')
                        ->formatStateUsing(fn ($state): string => $state instanceof TipoPrecioEspacio ? $state->getLabel() : TipoPrecioEspacio::tryFrom($state)?->getLabel() ?? $state)
                        ->sortable(),
                ]
                : [],

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
                ->color(fn ($state) => PrecioEstado::colorFor($state ?? '') ?? 'gray')
                ->formatStateUsing(fn ($state): string => PrecioEstado::labelFor($state ?? ''))
                ->sortable(),
        ];
    }
}
