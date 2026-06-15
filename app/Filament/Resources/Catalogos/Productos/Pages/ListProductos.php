<?php

namespace App\Filament\Resources\Catalogos\Productos\Pages;

use App\Actions\Catalogos\GenerarEtiquetasCodigosBarrasAction;
use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Resources\Catalogos\Productos\ProductoResource;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\UseCases\Catalogos\Queries\ExportProductosUseCase;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        $sharedFilters = [
            Select::make('categoria_id')
                ->label('Categoría')
                ->options(Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CATEGORIA_PRODUCTO->value))->pluck('nombre', 'id'))
                ->searchable(),
            Select::make('marca_id')
                ->label('Marca')
                ->options(Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::MARCA->value))->pluck('nombre', 'id'))
                ->searchable(),
            Select::make('tipo')
                ->label('Tipo de Producto')
                ->options([
                    1 => 'Perecedero',
                    2 => 'No perecedero',
                ]),
            Select::make('estado')
                ->label('Estado')
                ->options([
                    1 => 'Activo',
                    0 => 'Inactivo',
                ]),
        ];

        return [
            CreateAction::make(),
            Action::make('importar')
                ->label('Importar')
                ->icon(Heroicon::ArrowDownOnSquareStack)
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo CSV')
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(20480),
                ])
                ->action(function () {
                    session()->flash('success', 'Importación terminada.');
                }),

            Action::make('excel')
                ->label('Excel')
                ->icon(Heroicon::TableCells)
                ->color('success')
                ->schema($sharedFilters)
                ->action(function (array $data) {
                    $useCase = new ExportProductosUseCase;
                    $path = $useCase->exportarCsv($data);

                    return response()->download($path);
                }),

            ActionGroup::make([
                Action::make('simple')
                    ->label('Reporte Simple (CP-001)')
                    ->icon(Heroicon::Document)
                    ->schema($sharedFilters)
                    ->action(function (array $data) {
                        $action = new GenerarReporteProductosAction;
                        $pdf = $action->ejecutar($data, incluirVariantes: false);

                        return response()->streamDownload(fn () => print ($pdf->output()), 'HTB-CP001-Simple.pdf');
                    }),

                Action::make('detallado')
                    ->label('Reporte Detallado (CP-002)')
                    ->icon(Heroicon::DocumentCheck)
                    ->schema($sharedFilters)
                    ->action(function (array $data) {
                        $action = new GenerarReporteProductosAction;
                        $pdf = $action->ejecutar($data);

                        return response()->streamDownload(fn () => print ($pdf->output()), 'HTB-CP002-Detallado.pdf');
                    }),

                Action::make('etiquetas')
                    ->label('Etiquetas (CP-003)')
                    ->icon(Heroicon::QrCode)
                    ->schema([
                        Select::make('producto_id')
                            ->label('Filtrar por Producto')
                            ->options(Producto::pluck('nombre', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $action = new GenerarEtiquetasCodigosBarrasAction;
                        $pdf = $action->ejecutar($data['producto_id'] ?? null);

                        return response()->streamDownload(fn () => print ($pdf->output()), 'HTB-CP003-Etiquetas.pdf');
                    }),
            ])
                ->label('PDF')
                ->icon('heroicon-m-document-text')
                ->button()
                ->color('danger'),
        ];
    }
}
