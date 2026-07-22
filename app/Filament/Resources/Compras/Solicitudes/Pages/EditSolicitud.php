<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Interactors\Compras\Solicitudes\CancelarSolicitud;
use App\Repository\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSolicitud extends EditRecord
{
    protected CancelarSolicitud $cancelarSolicitud;

    public function boot(CancelarSolicitud $cancelarSolicitud): void
    {
        $this->cancelarSolicitud = $cancelarSolicitud;
    }

    protected static string $resource = SolicitudResource::class;

    protected function beforeFill(): void
    {
        /** @var Solicitud $record */
        $record = $this->getRecord();

        if ($record->estado !== EstadoSolicitud::Borrador) {
            Notification::make()
                ->warning()
                ->title('No editable')
                ->body('Solo se pueden editar solicitudes en estado Borrador.')
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelar')
                ->label('Cancelar Solicitud')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Aprobada;
                })
                ->schema(function (): array {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return [
                        Repeater::make('items_cancelacion')
                            ->label('Productos solicitados')
                            ->schema([
                                TextInput::make('producto_nombre')
                                    ->label('Producto')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('cantidad_solicitada')
                                    ->label('Solicitado')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('cantidad_aprobada')
                                    ->label('Aprobado')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->default(fn () => $record->items->map(fn ($item) => [
                                'producto_nombre' => $item->producto?->nombre,
                                'cantidad_solicitada' => $item->cantidad_solicitada,
                                'cantidad_aprobada' => 0,
                            ])->toArray())
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),

                        Textarea::make('nota_compras')
                            ->label('Nota del departamento de compras')
                            ->placeholder('Motivo de la cancelación')
                            ->required(),
                    ];
                })
                ->action(function (array $data) {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    $this->cancelarSolicitud->ejecutar(
                        $record,
                        $data['items_cancelacion'],
                        $data['nota_compras']
                    );

                    Notification::make()
                        ->title('Solicitud cancelada')
                        ->danger()
                        ->send();
                }),

            ViewAction::make(),
            DeleteAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
            ForceDeleteAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
            RestoreAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
        ];
    }
}
