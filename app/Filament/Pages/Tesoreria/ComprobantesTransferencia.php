<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Shared\Actions\VerGuiaAction;
use App\Interactors\Tesoreria\GestionarComprobantes;
use App\Repository\Models\Tesoreria\ComprobanteTransferencia;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use UnitEnum;

final class ComprobantesTransferencia extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Cobros y documentos';

    protected static ?string $navigationLabel = 'Comprobantes pendientes';

    protected static ?string $title = 'Comprobantes pendientes';

    protected static ?string $slug = 'tesoreria/comprobantes';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.tesoreria.comprobantes';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && ((auth()->user()?->can('Revisar:Comprobante') ?? false) || (auth()->user()?->can('Auditar:Fondos') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->comprobantes())->columns([TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->searchable(), TextColumn::make('usuario.name')->label('Cliente'), TextColumn::make('fondo.nombre')->label('Banco'), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('cuenta.moneda.codigo')->label('Moneda'), TextColumn::make('referencia')->searchable(), TextColumn::make('fecha')->date('d/m/Y'), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => match ($state) {
            'pendiente' => 'Pendiente de revisión','aprobado' => 'Pago aplicado','rechazado' => 'Rechazado','aclaracion' => 'Aclaración solicitada',default => $state
        })->badge()])->recordActions([
            Action::make('evidencias')->label('Evidencias e historial')->modalSubmitAction(false)->modalCancelActionLabel('Cerrar')->modalContent(fn (?ComprobanteTransferencia $record): View => view('filament.pages.tesoreria.evidencias', ['evidencias' => $record !== null ? app(TesoreriaQuery::class)->evidencias($record->id) : [], 'decisiones' => $record !== null ? app(TesoreriaQuery::class)->decisiones($record->id) : []])),
            Action::make('revisar')->extraModalFooterActions([VerGuiaAction::make('comprobante-revision')])->label('Revisar')->visible(fn (ComprobanteTransferencia $record): bool => $record->estado === 'pendiente' && $this->permitido('Revisar:Comprobante'))->schema([$this->claveOperacion(), Select::make('decision')->extraAttributes(['data-guia' => 'comprobante-decision'])->options(['aprobado' => 'Aprobar contra el banco', 'rechazado' => 'Rechazar', 'aclaracion' => 'Pedir nueva evidencia'])->live()->required(), Select::make('extracto_id')->extraAttributes(['data-guia' => 'comprobante-banco'])->label('Movimiento bancario comprobado')->options(fn (): array => app(TesoreriaQuery::class)->extractosPendientes())->searchable()->required(fn (Get $get): bool => $get('decision') === 'aprobado')->visible(fn (Get $get): bool => $get('decision') === 'aprobado'), Toggle::make('emitir_factura')->label('Emitir factura al liquidar')->default(false)->live()->visible(fn (): bool => $this->permitido('Create:Factura')),
                Select::make('serie_id')->label('Serie de factura')->options(fn (): array => app(OperacionCajaQuery::class)->series())->visible(fn (Get $get): bool => (bool) $get('emitir_factura'))->required(fn (Get $get): bool => (bool) $get('emitir_factura')),
                Textarea::make('motivo')->extraAttributes(['data-guia' => 'comprobante-motivo'])->minLength(10)->maxLength(1000)->required()])->action(function (ComprobanteTransferencia $record, array $data): void {
                    $this->operar(fn () => app(GestionarComprobantes::class)->revisar($this->actorId(), $record->id, $this->texto($data, 'decision'), $this->texto($data, 'motivo'), $this->texto($data, 'clave'), $this->idOpcional($data, 'extracto_id'), (bool) ($data['emitir_factura'] ?? false), $this->idOpcional($data, 'serie_id')), 'Decisión conservada en el historial');
                }),
        ]);
    }
}
