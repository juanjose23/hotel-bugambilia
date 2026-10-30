<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarCajaChica;
use App\Repository\Models\Tesoreria\ValeCajaChica;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class CajaChica extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected static string|UnitEnum|null $navigationGroup = 'Fondos y bancos';

    protected static ?string $navigationLabel = 'Caja chica';

    protected static ?string $title = 'Caja chica';

    protected static ?string $slug = 'tesoreria/caja-chica';

    protected static ?int $navigationSort = 33;

    protected string $view = 'filament.pages.tesoreria.caja-chica';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Fondos') ?? false) || (auth()->user()?->can('Operar:CajaChica') ?? false) || (auth()->user()?->can('Aprobar:CajaChica') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->vales(auth()->user() ?? throw new \DomainException('Inicia sesión.')))->columns([TextColumn::make('id')->label('Vale'), TextColumn::make('fondo.nombre')->label('Fondo'), TextColumn::make('beneficiario')->wrap(), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('fondo.moneda.codigo')->label('Moneda'), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => match ($state) {
            'solicitado' => 'Pendiente de autorización','entregado' => 'Por liquidar','liquidado' => 'Liquidado','rechazado' => 'Rechazado',default => $state
        })->badge(), TextColumn::make('vence_at')->label('Liquidar antes de')->date('d/m/Y'), TextColumn::make('gastado')->numeric(decimalPlaces: 2), TextColumn::make('devuelto')->numeric(decimalPlaces: 2)])->recordActions([
            Action::make('resolver')->label('Revisar vale')->visible(fn (ValeCajaChica $record): bool => $record->estado === 'solicitado' && ($this->permitido('Aprobar:CajaChica')))->schema([$this->claveOperacion(), Select::make('decision')->options(['aprobar' => 'Aprobar y entregar efectivo', 'rechazar' => 'Rechazar'])->required(), Textarea::make('motivo')->minLength(10)->maxLength(1000)->required()])->action(function (ValeCajaChica $record, array $data): void {
                $this->operar(fn () => app(GestionarCajaChica::class)->resolver($this->actorId(), $record->id, $this->texto($data, 'decision') === 'aprobar', $this->texto($data, 'motivo'), $this->texto($data, 'clave')), 'Revisión del vale registrada');
            }),
            Action::make('liquidar')->label('Liquidar y recibir remanente')->visible(fn (ValeCajaChica $record): bool => $record->estado === 'entregado' && ($this->permitido('Aprobar:CajaChica')))->modalDescription('Registra el gasto respaldado y confirma la recepción física del dinero no usado.')->schema([$this->claveOperacion(), TextInput::make('gastado')->label('Gasto respaldado')->numeric()->minValue(0)->step(.01)->required(), TextInput::make('referencia')->label('Referencia del comprobante')->maxLength(150)->required(), $this->soporte()])->action(function (ValeCajaChica $record, array $data): void {
                $this->operar(fn () => app(GestionarCajaChica::class)->liquidar($this->actorId(), $record->id, $this->numero($data, 'gastado'), $this->texto($data, 'referencia'), $this->texto($data, 'soporte'), $this->texto($data, 'clave')), 'Vale liquidado; remanente incorporado al fondo');
            }),
            Action::make('soporte')->label('Ver comprobante')->visible(fn (ValeCajaChica $record): bool => $record->soporte !== null)->url(fn (ValeCajaChica $record): string => route('tesoreria.archivo', ['tipo' => 'vale', 'id' => $record->id]))->openUrlInNewTab(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('solicitar')->label('Solicitar vale')->visible(fn (): bool => $this->permitido('Operar:CajaChica'))->schema([$this->claveOperacion(), Select::make('fondo_id')->label('Mi fondo de caja chica')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos('caja_chica', $this->actorId()))->required(), TextInput::make('beneficiario')->maxLength(150)->required(), TextInput::make('importe')->numeric()->minValue(.01)->step(.01)->required(), DatePicker::make('vence')->label('Fecha de liquidación')->minDate(today())->required(), Textarea::make('concepto')->minLength(10)->maxLength(1000)->required()])->action(function (array $data): void {
            $this->operar(fn () => app(GestionarCajaChica::class)->solicitar($this->actorId(), $this->idDato($data, 'fondo_id'), $this->texto($data, 'beneficiario'), $this->numero($data, 'importe'), $this->texto($data, 'concepto'), $this->texto($data, 'vence'), $this->texto($data, 'clave')), 'Vale pendiente de aprobación');
        })];
    }
}
