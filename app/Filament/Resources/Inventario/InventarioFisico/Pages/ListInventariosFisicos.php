<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Pages;

use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\InventarioFisico;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInventariosFisicos extends ListRecords
{
    protected static string $resource = InventarioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('iniciar_conteo_rapido')
                ->label('Iniciar Conteo Rápido')
                ->icon(Heroicon::Bolt)
                ->color('primary')
                ->modalHeading('Iniciar Toma Rápida de Inventario Físico')
                ->modalDescription('Seleccione el almacén o categoría a auditar para precargar la hoja de cálculo interactiva de forma ágil y enfocada.')
                ->modalSubmitActionLabel('Iniciar Conteo ➜')
                ->schema([
                    Select::make('ubicacion_id')
                        ->label('Ubicación / Almacén a Auditar')
                        ->placeholder('Todas las Ubicaciones (Hotel Completo)')
                        ->options(fn () => Ubicacion::query()->orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->nullable()
                        ->prefixIcon(Heroicon::BuildingOffice),

                    Select::make('categoria_id')
                        ->label('Categoría de Producto (Opcional)')
                        ->placeholder('Todas las Categorías')
                        ->options(fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CATEGORIA_PRODUCTO'))->orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->nullable()
                        ->prefixIcon(Heroicon::Tag),

                    Toggle::make('precargar_igual')
                        ->label('Precargar cantidades físicas con stock del sistema')
                        ->helperText('Ahorra tiempo si la mayoría de existencias coinciden; solo tendrá que editar las diferencias.')
                        ->default(true),

                    Toggle::make('conteo_ciego')
                        ->label('Modo Conteo Ciego (Ocultar Stock del Sistema)')
                        ->helperText('Oculta los números teóricos para una auditoría sin sesgo.')
                        ->default(false),

                    Textarea::make('observaciones')
                        ->label('Observaciones Iniciales')
                        ->placeholder('Ej: Auditoría rápida de cierre de turno vespertino...'),
                ])
                ->action(function (array $data): void {
                    $ubicacionId = ! empty($data['ubicacion_id']) ? (int) $data['ubicacion_id'] : null;
                    $categoriaId = ! empty($data['categoria_id']) ? (int) $data['categoria_id'] : null;
                    $precargarIgual = (bool) ($data['precargar_igual'] ?? true);
                    $conteoCiego = (bool) ($data['conteo_ciego'] ?? false);

                    $hoja = InventarioFisico::generarHojaInicial(
                        ubicacionId: $ubicacionId,
                        categoriaId: $categoriaId,
                        precargarIgual: $precargarIgual,
                        conteoCiego: $conteoCiego,
                    );

                    $userId = auth()->id();
                    $userIdInt = is_numeric($userId) ? (int) $userId : null;

                    $inventario = InventarioFisico::query()->create([
                        'fecha_toma' => now()->toDateString(),
                        'creado_por_id' => $userIdInt,
                        'ubicacion_id' => $ubicacionId,
                        'observaciones' => $data['observaciones'] ?? null,
                        'datos_hoja' => $hoja,
                    ]);

                    Notification::make()
                        ->title('Toma de Inventario Iniciada')
                        ->body("Se ha iniciado la toma {$inventario->codigo} exitosamente.")
                        ->success()
                        ->send();

                    $this->redirect(InventarioFisicoResource::getUrl('view', ['record' => $inventario]));
                }),

            CreateAction::make()
                ->label('Formulario Completo')
                ->icon(Heroicon::Plus)
                ->color('gray'),
        ];
    }
}
