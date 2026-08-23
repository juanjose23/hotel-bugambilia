<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\BusinessLogic\Shared\ServicioPrecios;
use App\Enums\HabitacionesEspacios\TipoPrecioEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Interactors\Shared\AsignarPrecio;
use App\Repository\Queries\Shared\VerificarPrecioDuplicado;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait TieneFormularioPrecios
{
    use TieneAccionesCrudEstandar;

    protected VerificarPrecioDuplicado $verificarPrecioDuplicado;

    protected AsignarPrecio $asignarPrecio;

    protected ServicioPrecios $servicioPrecios;

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
                    ->rule(fn (callable $get): string => intval($get('estado')) === EstadoGeneral::Activo->value ? 'min:0.01' : 'min:0'),

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
                    ->options(EstadoGeneral::options())
                    ->default(EstadoGeneral::Activo->value)
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

    public function getUniquePrecioVigenteRule(): Closure
    {
        return fn (callable $get, $component): Closure => function (string $attribute, $value, Closure $fail) use ($get, $component) {
            $estado = intval($get('estado'));
            $esOferta = (bool) $get('es_oferta');

            if ($estado !== 1 || $esOferta) {
                return;
            }

            $monedaId = intval($get('moneda_id'));
            if (! $monedaId) {
                return;
            }

            $parentRecord = $this->getOwnerRecord();
            $modelClass = $this->getPriceableModelClass();

            $tipoPrecio = $this->hasTipoPrecioField() ? $get('tipo_precio') : null;
            if ($this->hasTipoPrecioField() && ! $tipoPrecio) {
                return;
            }

            $record = $component->getRecord();
            $excludeId = ($record && $record->exists) ? $record->getKey() : null;
            $parentKey = $parentRecord->getKey();

            $duplicado = $this->verificarPrecioDuplicado->ejecutar(
                modelClass: $modelClass,
                parentId: is_scalar($parentKey) ? intval($parentKey) : 0,
                monedaId: $monedaId,
                foreignKey: $this->getPriceableForeignKey(),
                foreignType: $this->getPriceableForeignType(),
                parentType: $parentRecord::class,
                excludeId: is_scalar($excludeId) ? intval($excludeId) : null,
                tipoPrecio: $tipoPrecio,
            );

            if ($duplicado) {
                $fail($this->getUniquePrecioErrorMessage());
            }
        };
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

    /** @return list<Column> */
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
                        ->color(fn ($state): string => match ($state instanceof TipoPrecioEspacio ? $state : TipoPrecioEspacio::tryFrom((string) $state)) {
                            TipoPrecioEspacio::Base => 'success',
                            TipoPrecioEspacio::PorHora => 'warning',
                            default => 'gray',
                        })
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

            EstadoBadgeColumn::make(EstadoGeneral::class)
                ->sortable(),
        ];
    }

    /** @return list<Action> */
    protected function getStandardHeaderActions(?string $label = null): array
    {
        return [
            CreateAction::make()
                ->label($label ?? $this->getCreateActionLabel())
                ->icon(Heroicon::Plus)
                ->using(function (array $data): Model {
                    $owner = $this->getOwnerRecord();
                    $ownerKey = $owner->getKey();

                    return $this->asignarPrecio->execute(
                        priceableType: $owner::class,
                        priceableId: is_scalar($ownerKey) ? intval($ownerKey) : 0,
                        monedaId: (int) $data['moneda_id'],
                        precio: (float) $data['precio'],
                        fechaInicio: (string) $data['fecha_inicio'],
                        fechaFin: empty($data['fecha_fin']) ? null : (string) $data['fecha_fin'],
                        estado: (int) $data['estado'],
                        esOferta: (bool) ($data['es_oferta'] ?? false),
                        tipoPrecio: $this->hasTipoPrecioField() ? (string) $data['tipo_precio'] : 'base',
                    );
                }),
        ];
    }

    /** @return list<Action> */
    protected function getStandardRowActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->using(function (Model $record, array $data): Model {
                    DB::transaction(function () use ($record, $data) {
                        $estado = (int) $data['estado'];
                        $esOferta = (bool) ($data['es_oferta'] ?? false);
                        $monedaId = (int) $data['moneda_id'];
                        $tipoPrecio = $this->hasTipoPrecioField() ? (string) $data['tipo_precio'] : 'base';

                        $priceableType = $record->getAttribute('priceable_type');
                        $priceableId = $record->getAttribute('priceable_id');

                        $this->servicioPrecios->expirarPreciosAnterioresSiCorresponde(
                            priceableType: is_string($priceableType) ? $priceableType : '',
                            priceableId: is_scalar($priceableId) ? intval($priceableId) : 0,
                            monedaId: $monedaId,
                            tipoPrecio: $tipoPrecio,
                            estado: $estado,
                            esOferta: $esOferta,
                        );

                        $record->update([
                            'moneda_id' => $monedaId,
                            'precio' => (float) $data['precio'],
                            'fecha_inicio' => (string) $data['fecha_inicio'],
                            'fecha_fin' => empty($data['fecha_fin']) ? null : (string) $data['fecha_fin'],
                            'estado' => $estado,
                            'es_oferta' => $esOferta,
                            'tipo_precio' => $tipoPrecio,
                        ]);
                    });

                    return $record;
                }),
            DeleteAction::make()->iconButton(),
        ];
    }
}
