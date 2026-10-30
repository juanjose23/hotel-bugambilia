<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class MovimientosFondos extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja,DatosTesoreria,InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Fondos y bancos';

    protected static ?string $navigationLabel = 'Libro de fondos';

    protected static ?string $title = 'Libro de fondos';

    protected static ?string $slug = 'tesoreria/libro-fondos';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.tesoreria.libro-fondos';

    public static function canAccess(): bool
    {
        return (bool) config('tesoreria.habilitado') && (auth()->user()?->can('ViewAny:Fondos') ?? false);
    }

    public function table(Table $table): Table
    {
        return $table->query(app(TesoreriaQuery::class)->movimientos())->columns([TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'), TextColumn::make('fondo.nombre')->label('Fondo'), TextColumn::make('fondo.moneda.codigo')->label('Moneda'), TextColumn::make('importe')->numeric(decimalPlaces: 2), TextColumn::make('concepto')->wrap(), TextColumn::make('referencia')->searchable(), TextColumn::make('usuario_id')->label('Usuario')])->filters([SelectFilter::make('fondo_id')->label('Fondo')->options(fn (): array => app(TesoreriaQuery::class)->opcionesFondos())]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('exportar')->label('Descargar auditoría CSV')->visible(fn (): bool => $this->permitido('Auditar:Fondos'))->url(route('tesoreria.auditoria'))];
    }
}
