<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Productos;

use App\BusinessLogic\Shared\Reportes\ContarRegistrosReporte;
use App\Filament\Resources\Catalogos\Productos\Pages\CreateProducto;
use App\Filament\Resources\Catalogos\Productos\Pages\EditProducto;
use App\Filament\Resources\Catalogos\Productos\Pages\ListProductos;
use App\Filament\Resources\Catalogos\Productos\Pages\ViewProducto;
use App\Filament\Resources\Catalogos\Productos\Schemas\ProductoForm;
use App\Filament\Resources\Catalogos\Productos\Schemas\ProductoInfolist;
use App\Filament\Resources\Catalogos\Productos\Tables\ProductosTable;
use App\Interactors\Catalogos\Productos\GenerarReporteProductos;
use App\Jobs\GenerarReporteJob;
use App\Repository\Models\Catalogos\Producto;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario & Productos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ProductoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantesRelationManager::class,
            RelationManagers\KitsRelationManager::class,
        ];
    }

    /** @return array<int, Action> */
    public static function getActions(): array
    {

        return [
            Action::make('exportar_excel')
                ->label('Exportar Excel')
                ->icon(Heroicon::TableCells)
                ->color('success')
                ->action(function () {
                    return app(GenerarReporteProductos::class)->excel([]);
                }),

            Action::make('descargar_reporte_pdf')
                ->label('Reporte PDF (CP-001/002)')
                ->icon(Heroicon::Document)
                ->color('danger')
                ->action(function (array $data) {
                    $contador = app(ContarRegistrosReporte::class);
                    if ($contador->superaUmbral('HTB-CP002', $data)) {
                        dispatch(new GenerarReporteJob(
                            codigoReporte: 'HTB-CP002',
                            parametros: $data,
                            usuarioId: (int) auth()->id(),
                        ));
                        Notification::make()
                            ->title('⏳ Reporte en segundo plano')
                            ->body('El reporte contiene un volumen alto de registros para generarse en tiempo real. Se está procesando en segundo plano y recibirás una notificación cuando esté listo para descargar.')
                            ->warning()
                            ->duration(10000)
                            ->send();

                        return null;
                    }

                    $pdf = app(GenerarReporteProductos::class)->detallado($data);

                    return response()->streamDownload(fn () => print ($pdf->output()), 'HTB-CP-Reporte-'.now()->format('Ymd_His').'.pdf');
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductos::route('/'),
            'create' => CreateProducto::route('/create'),
            'view' => ViewProducto::route('/{record}'),
            'edit' => EditProducto::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
