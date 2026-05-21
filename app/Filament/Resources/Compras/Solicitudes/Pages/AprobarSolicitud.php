<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Solicitud;
use App\UseCases\Compras\Solicitudes\Mutations\AprobarSolicitud as AprobarSolicitudUseCase;
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
    protected static string $resource = SolicitudResource::class;

    /** @var Solicitud */
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

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->validate();

        $data = $this->form->getState();

        app(AprobarSolicitudUseCase::class)->execute($this->record, $data['items_aprobados'] ?? []);

        if ($shouldSendSavedNotification) {
            Notification::make()
                ->success()
                ->title('Solicitud aprobada')
                ->send();
        }

        if ($shouldRedirect) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    public function aceptarTodasLasCantidades(): void
    {
        $items = $this->data['items_aprobados'] ?? [];
        foreach ($items as $key => $item) {
            $items[$key]['cantidad_aprobada'] = $item['cantidad_solicitada'] ?? 0;
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
