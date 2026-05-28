<?php

namespace App\Filament\Resources\Catalogos\Productos;

use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\Filament\Resources\Catalogos\Productos\Pages\CreateProducto;
use App\Filament\Resources\Catalogos\Productos\Pages\EditProducto;
use App\Filament\Resources\Catalogos\Productos\Pages\ListProductos;
use App\Filament\Resources\Catalogos\Productos\Pages\ViewProducto;
use App\Filament\Resources\Catalogos\Productos\Schemas\ProductoForm;
use App\Filament\Resources\Catalogos\Productos\Schemas\ProductoInfolist;
use App\Filament\Resources\Catalogos\Productos\Tables\ProductosTable;
use App\Models\Catalogos\Producto;
use App\UseCases\Catalogos\Queries\ExportProductosUseCase;
use BackedEnum;
use Filament\Actions\Action;
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

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

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
                    $useCase = new ExportProductosUseCase;
                    $path = $useCase->exportarCsv();

                    return response()->download($path);
                }),

            Action::make('descargar_reporte_pdf')
                ->label('Reporte PDF (CP-001/002)')
                ->icon(Heroicon::Document)
                ->color('danger')
                ->action(function () {
                    $action = new GenerarReporteProductosAction;
                    $pdf = $action->ejecutar();

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
