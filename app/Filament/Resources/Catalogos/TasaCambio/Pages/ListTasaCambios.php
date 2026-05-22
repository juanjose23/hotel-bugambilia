<?php

namespace App\Filament\Resources\Catalogos\TasaCambio\Pages;

use App\Filament\Resources\Catalogos\TasaCambio\TasaCambioResource;
use App\Filament\Resources\Catalogos\TasaCambio\Widgets\TasaCambioHoyWidget;
use App\Imports\TasaCambioImport;
use App\Models\Monedas\Moneda;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ListTasaCambios extends ListRecords
{
    protected static string $resource = TasaCambioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importarExcel')
                ->label('Importar Excel')
                ->icon(Heroicon::DocumentArrowUp)
                ->color('info')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('Archivo Excel (.xlsx, .xls)')
                        ->rules(['file', 'extensions:xls,xlsx'])
                        ->required()
                        ->disk('local')
                        ->directory('imports'),

                    Select::make('moneda_origen_id')
                        ->label('Moneda de Origen')
                        ->options(Moneda::pluck('nombre', 'id'))
                        ->required()
                        ->default(fn () => Moneda::where('codigo', 'USD')->first()?->id)
                        ->searchable()
                        ->live(),

                    Select::make('moneda_destino_id')
                        ->label('Moneda de Destino')
                        ->options(fn ($get) => Moneda::where('id', '!=', $get('moneda_origen_id'))
                            ->pluck('nombre', 'id')
                        )
                        ->required()
                        ->default(fn () => Moneda::where('es_predeterminada', true)->first()?->id)
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $filePath = Storage::disk('local')->path($data['excel_file']);

                    try {
                        $readerType = IOFactory::identify($filePath);

                        // Si el archivo es identificado como HTML/XML (común en exportaciones falsas de .xls),
                        // desactivamos temporalmente los errores de libxml para evitar excepciones fatales de DOM.
                        $isHtmlOrXml = in_array($readerType, ['Html', 'Xml']);
                        if ($isHtmlOrXml) {
                            libxml_use_internal_errors(true);
                        }

                        $import = new TasaCambioImport($data['moneda_origen_id'], $data['moneda_destino_id']);
                        Excel::import($import, $filePath, null, $readerType);

                        if ($isHtmlOrXml) {
                            libxml_clear_errors();
                        }

                        $count = $import->getImportedCount();

                        Notification::make()
                            ->title('Tasas de Cambio Importadas')
                            ->body("Se han importado y actualizado exitosamente {$count} tasas de cambio.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        if (str_contains($msg, 'DOM Document') || str_contains($msg, 'HTML') || str_contains($msg, 'OLE')) {
                            $msg = 'El archivo subido parece ser una tabla HTML o exportación antigua renombrada como .xls. Por favor, abre el archivo en Excel y guárdalo usando "Guardar como -> Libro de Excel (*.xlsx)" para importarlo correctamente.';
                        }

                        Notification::make()
                            ->title('Error al importar')
                            ->body($msg)
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Registrar Tasa')
                ->icon(Heroicon::Plus),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TasaCambioHoyWidget::class,
        ];
    }
}
