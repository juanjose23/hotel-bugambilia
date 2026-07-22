<?php

namespace App\Filament\Resources\Catalogos\Productos\Pages;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Resources\Catalogos\Productos\ProductoResource;
use App\Interactors\Catalogos\ExportarProductosInteractor;
use App\Interactors\Catalogos\ImportarProductos;
use App\Interactors\Catalogos\Reportes\Productos\GenerarReporteProductosInteractor;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Support\Pdf\FormatoPagina;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        $sharedFilters = fn (): array => [
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
                ->options(EstadoGeneral::options()),
        ];

        $pdfFilters = fn (): array => [
            ...$sharedFilters(),
            ToggleButtons::make('tipo_pagina')
                ->label('Tipo de página')
                ->options(FormatoPagina::options())
                ->default(FormatoPagina::A4_Vertical->value)
                ->columns(3)
                ->required(),
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
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->maxSize(20480),
                ])
                ->action(function (array $data) {
                    $archivo = $data['archivo'] ?? null;
                    if ($archivo) {
                        try {
                            $path = Storage::disk('local')->path($archivo);
                            $res = app(ImportarProductos::class)->ejecutar($path);

                            if (count($res['errors']) > 0) {
                                Notification::make()
                                    ->title('Importación completada con observaciones')
                                    ->body("Se procesaron {$res['processed']} productos. Ocurrieron ".count($res['errors']).' errores.')
                                    ->warning()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Importación exitosa')
                                    ->body("Se importaron exitosamente {$res['processed']} productos.")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error en importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }
                }),

            Action::make('excel')
                ->label('Excel')
                ->icon(Heroicon::TableCells)
                ->color('success')
                ->schema($sharedFilters())
                ->action(function (array $data) {
                    $path = app(ExportarProductosInteractor::class)->ejecutar($data);

                    return response()->download($path);
                }),

            ActionGroup::make([
                Action::make('simple')
                    ->label('Reporte Simple (CP-001)')
                    ->icon(Heroicon::Document)
                    ->schema([
                        ...$pdfFilters(),
                        Checkbox::make('background')
                            ->label('Generar en segundo plano (notificar cuando esté listo)')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        if (! empty($data['background'])) {
                            dispatch(new GenerarReporteJob(
                                codigoReporte: 'HTB-CP001',
                                parametros: $data,
                                usuarioId: (int) auth()->id(),
                            ));
                            Notification::make()
                                ->title('Reporte en proceso')
                                ->body('Recibirás una notificación cuando esté listo.')
                                ->success()
                                ->send();

                            return;
                        }
                        $pdf = app(GenerarReporteProductosInteractor::class)->simple($data);

                        return response()->stream(fn () => print ($pdf->output()), 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="HTB-CP001-Simple.pdf"',
                        ]);
                    }),

                Action::make('detallado')
                    ->label('Reporte Detallado (CP-002)')
                    ->icon(Heroicon::DocumentCheck)
                    ->schema([
                        ...$pdfFilters(),
                        Checkbox::make('background')
                            ->label('Generar en segundo plano (notificar cuando esté listo)')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        if (! empty($data['background'])) {
                            dispatch(new GenerarReporteJob(
                                codigoReporte: 'HTB-CP002',
                                parametros: $data,
                                usuarioId: (int) auth()->id(),
                            ));
                            Notification::make()
                                ->title('Reporte en proceso')
                                ->body('Recibirás una notificación cuando esté listo.')
                                ->success()
                                ->send();

                            return;
                        }
                        $pdf = app(GenerarReporteProductosInteractor::class)->detallado($data);

                        return response()->stream(fn () => print ($pdf->output()), 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="HTB-CP002-Detallado.pdf"',
                        ]);
                    }),

                Action::make('etiquetas')
                    ->label('Etiquetas (CP-003)')
                    ->icon(Heroicon::QrCode)
                    ->schema([
                        ...$sharedFilters(),
                        Select::make('producto_id')
                            ->label('Filtrar por Producto (opcional)')
                            ->options(Producto::pluck('nombre', 'id'))
                            ->searchable(),
                        ToggleButtons::make('tipo_pagina')
                            ->label('Tipo de página')
                            ->options(FormatoPagina::options())
                            ->default(FormatoPagina::A4_Vertical->value)
                            ->columns(3)
                            ->required(),
                        Checkbox::make('background')
                            ->label('Generar en segundo plano (notificar cuando esté listo)')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        if (! empty($data['background'])) {
                            dispatch(new GenerarReporteJob(
                                codigoReporte: 'HTB-CP003',
                                parametros: $data,
                                usuarioId: (int) auth()->id(),
                            ));
                            Notification::make()
                                ->title('Reporte en proceso')
                                ->body('Recibirás una notificación cuando esté listo.')
                                ->success()
                                ->send();

                            return;
                        }
                        $pdf = app(GenerarReporteProductosInteractor::class)->etiquetas($data);

                        return response()->stream(fn () => print ($pdf->output()), 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="HTB-CP003-Etiquetas.pdf"',
                        ]);
                    }),
            ])
                ->label('PDF')
                ->icon('heroicon-m-document-text')
                ->button()
                ->color('danger'),
        ];
    }
}
