<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages;

use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use App\Filament\Shared\Actions\Restaurante\ReporteCostosCocinaAction;
use App\Interactors\Restaurante\Cocina\ConfirmarProduccionCocina;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Queries\Restaurante\Cocina\ControlOperacionCocinaQuery;
use App\Repository\Queries\Restaurante\Cocina\GuiaRecetaCocinaQuery;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class EditProcesoCocina extends EditRecord
{
    protected static ?string $title = 'Continuar preparación';

    protected static string $resource = ProcesoCocinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmarProduccion')
                ->label('Confirmar preparación')->color('success')->beforeFormFilled(function (): void {
                    $record = $this->getRecord();
                    if ((is_numeric($this->data['plato_id'] ?? null) ? (string) $this->data['plato_id'] : '') !== (is_numeric($record->getAttribute('plato_id')) ? (string) $record->getAttribute('plato_id') : '') || (is_numeric($this->data['cantidad_platos'] ?? null) ? (string) $this->data['cantidad_platos'] : '') !== (is_numeric($record->getAttribute('cantidad_platos')) ? (string) $record->getAttribute('cantidad_platos') : '')) {
                        Notification::make()->title('Guarda primero los cambios de receta o porciones')->warning()->send();
                        throw new Halt;
                    }
                })->modalWidth('5xl')->modalHeading('Terminar la preparación paso a paso')->modalDescription('Revisa la bodega, registra lo obtenido y confirma una sola vez para actualizar las existencias.')->modalSubmitActionLabel('Confirmar y actualizar existencias')
                ->visible(fn (): bool => $this->getRecord()->getAttribute('confirmado_at') === null && $this->getRecord()->getAttribute('pedido_item_id') === null && $this->getRecord()->getAttribute('tipo_registro') === 'borrador')
                ->steps([
                    Step::make('Bodega e ingredientes')->description('Revisa antes de confirmar')->schema([
                        Select::make('ubicacion_id')->label('¿En qué bodega preparaste?')->options(fn (): array => app(ControlOperacionCocinaQuery::class)->ubicaciones())->live()->required(),
                        ViewField::make('guia')->view('filament.pages.restaurante.components.guia-ingredientes-receta')->dehydrated(false)->viewData(function (Get $get): array {
                            $id = $get('ubicacion_id');
                            $record = $this->getRecord();

                            return ['guia' => app(GuiaRecetaCocinaQuery::class)->ejecutar(is_numeric($record->getAttribute('plato_id')) ? (int) $record->getAttribute('plato_id') : null, is_numeric($record->getAttribute('cantidad_platos')) ? (int) $record->getAttribute('cantidad_platos') : 1, is_numeric($id) ? (int) $id : null)];
                        }),
                    ]),
                    Step::make('Resultado real')->description('Qué obtuviste y hasta cuándo usarlo')->schema([
                        Select::make('variante_id')->label('Producto terminado que obtuviste')->options(fn (): array => ProductoVariante::query()->with(['producto', 'unidadMedida'])->where('estado', 1)->get()->mapWithKeys(fn (ProductoVariante $variante): array => [$variante->id => ($variante->producto->nombre ?? '').' · '.$variante->nombre_variante.' / '.($variante->unidadMedida->nombre ?? 'unidades')])->all())->searchable()->live()->required(),
                        TextInput::make('cantidad')->label('Cantidad realmente obtenida')->helperText(fn (Get $get): string => 'Escribe la cantidad en '.app(ControlOperacionCocinaQuery::class)->seleccion(is_numeric($get('variante_id')) ? (int) $get('variante_id') : null, null)['unidad'].'.')->numeric()->minValue(0.0001)->required(),
                        DatePicker::make('vence')->label('Vencimiento de la preparación')->minDate(today())->required(),
                    ]),
                ])
                ->requiresConfirmation()
                ->action(function (array $data): void {
                    /** @var array{variante_id: int, ubicacion_id: int, cantidad: float, vence: string} $data */
                    try {
                        app(ConfirmarProduccionCocina::class)->ejecutar(is_numeric($this->getRecord()->getKey()) ? (int) $this->getRecord()->getKey() : 0, (int) $data['variante_id'], (int) $data['ubicacion_id'], (float) $data['cantidad'], (string) $data['vence'], auth()->id() !== null ? (int) auth()->id() : null);
                        Notification::make()->title('Producción confirmada')->success()->send();
                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\DomainException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),
            ReporteCostosCocinaAction::make(),
            DeleteAction::make()->visible(fn (): bool => $this->getRecord()->getAttribute('confirmado_at') === null && $this->getRecord()->getAttribute('pedido_item_id') === null && $this->getRecord()->getAttribute('tipo_registro') === 'borrador'),
            ForceDeleteAction::make()->visible(fn (): bool => $this->getRecord()->getAttribute('confirmado_at') === null && $this->getRecord()->getAttribute('pedido_item_id') === null && $this->getRecord()->getAttribute('tipo_registro') === 'borrador'),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ProcesoCocina $proceso */
        $proceso = $record;
        $userId = Auth::id() !== null ? (int) Auth::id() : null;

        return app(ProcesarProcesoCocina::class)->guardar($proceso, $data, $userId);
    }
}
