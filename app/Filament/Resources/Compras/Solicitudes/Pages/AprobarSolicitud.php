<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Interactors\Compras\Solicitudes\AprobarSolicitud as AprobarSolicitudUseCase;
use App\Repository\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class AprobarSolicitud extends EditRecord
{
    protected AprobarSolicitudUseCase $aprobarSolicitudUseCase;

    public function boot(AprobarSolicitudUseCase $aprobarSolicitudUseCase): void
    {
        $this->aprobarSolicitudUseCase = $aprobarSolicitudUseCase;
    }

    protected static string $resource = SolicitudResource::class;

    public Model|int|string|null $record = null;

    protected static ?string $title = 'Aprobar Solicitud';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        assert($this->record instanceof Solicitud);
        $this->record->loadMissing('items.producto', 'items.productoVariante');

        $this->fillForm();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        assert($this->record instanceof Solicitud);

        $data['items_aprobados'] = $this->record->items->map(fn ($item) => [
            'id' => $item->id,
            'producto_nombre' => $item->producto?->nombre,
            'variante' => $item->productoVariante?->codigo,
            'cantidad_solicitada' => $item->cantidad_solicitada,
            'cantidad_aprobada' => $item->cantidad_aprobada ?: $item->cantidad_solicitada,
        ])->toArray();

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('items_aprobados')
                    ->label('Productos')
                    ->schema([
                        TextInput::make('producto_nombre')
                            ->label('Producto')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('variante')
                            ->label('Variante')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('cantidad_solicitada')
                            ->label('Solicitado')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('cantidad_aprobada')
                            ->label('Aprobar')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Hidden::make('id'),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ]);
    }

    public function save(
        bool $shouldRedirect = true,
        bool $shouldSendSavedNotification = true,
    ): void {
        $this->validate();

        assert($this->record instanceof Solicitud);

        $this->aprobarSolicitudUseCase->ejecutar(
            $this->record,
            $this->obtenerItemsAprobados(),
        );

        if ($shouldSendSavedNotification) {
            Notification::make()
                ->title('Solicitud aprobada')
                ->success()
                ->send();
        }

        if ($shouldRedirect) {
            $this->redirect(
                static::getResource()::getUrl('view', [
                    'record' => $this->record,
                ]),
            );
        }
    }

    /**
     * @return list<array{id:int,cantidad_aprobada:int|float|string}>
     */
    private function obtenerItemsAprobados(): array
    {
        /** @var mixed $items */
        $items = $this->form->getState()['items_aprobados'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        $resultado = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? null;
            $cantidad = $item['cantidad_aprobada'] ?? 0;

            if (! is_numeric($id)) {
                continue;
            }
            $resultado[] = [
                'id' => (int) $id,
                'cantidad_aprobada' => is_numeric($cantidad)
                    ? $cantidad
                    : 0,
            ];
        }

        return $resultado;
    }

    public function aceptarTodasLasCantidades(): void
    {
        /** @var list<array<string, mixed>> $items */
        $items = $this->data['items_aprobados'] ?? [];
        foreach ($items as $key => $item) {
            /** @var array<string, mixed> $item */
            $cantidadSolicitada = $item['cantidad_solicitada'] ?? 0;
            $items[$key]['cantidad_aprobada'] = floatval(is_numeric($cantidadSolicitada) ? $cantidadSolicitada : 0);
        }
        $this->data['items_aprobados'] = $items;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aceptar_todas')
                ->label('Aceptar todas las cantidades')
                ->icon(Heroicon::CheckBadge)
                ->color('success')
                ->action('aceptarTodasLasCantidades'),
            Action::make('save')
                ->label('Confirmar Aprobación')
                ->icon(Heroicon::CheckCircle)
                ->color('primary')
                ->action('save'),
            Action::make('cancelar')
                ->label('Cancelar')
                ->url(fn () => $this->getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
