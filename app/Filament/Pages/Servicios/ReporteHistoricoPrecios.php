<?php

declare(strict_types=1);

namespace App\Filament\Pages\Servicios;

use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Forms\ServicioSelect;
use App\Support\CachedOptions;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReporteHistoricoPrecios extends Page
{
    use HasPageShield;

    protected string $view = 'filament.resources.servicios.reporte-historico-precios';

    protected static ?string $slug = 'reporte-historico-precios-servicios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Servicios';

    protected static ?string $navigationLabel = 'Histórico de Precios';

    protected static ?string $title = 'Histórico de Servicios por Precio por Moneda';

    protected static ?int $navigationSort = 99;

    public function getHeaderActions(): array
    {
        $sharedFilters = [
            Select::make('categoria_id')
                ->label('Filtrar por Categoría (Opcional)')
                ->options(fn () => CachedOptions::catalogos(CatalogoTipo::CATEGORIA_SERVICIO->value))
                ->searchable()
                ->placeholder('Todas las categorías'),
            ServicioSelect::make('servicio_id')
                ->placeholder('Todos los servicios'),
            Select::make('moneda_id')
                ->label('Filtrar por Moneda (Opcional)')
                ->options(fn () => CachedOptions::monedas())
                ->searchable()
                ->placeholder('Todas las monedas'),
            Select::make('estado')
                ->label('Estado del Precio')
                ->options([
                    '' => 'Todos',
                    '1' => 'Vigente',
                    '2' => 'No Vigente',
                ])
                ->default(''),
        ];

        return [
            Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon(Heroicon::DocumentText)
                ->color('danger')
                ->modalHeading('Histórico de Precios — PDF')
                ->modalDescription('Filtra opcionalmente y descarga el reporte en PDF listo para imprimir.')
                ->schema($sharedFilters)
                ->action(function (array $data) {
                    return redirect()->route('admin.servicios.reportes.historico-precios.pdf', [
                        'categoria_id' => $data['categoria_id'] ?? null,
                        'servicio_id' => $data['servicio_id'] ?? null,
                        'moneda_id' => $data['moneda_id'] ?? null,
                        'estado' => $data['estado'] ?: null,
                    ]);
                }),

            Action::make('descargar_excel')
                ->label('Descargar Excel')
                ->icon(Heroicon::TableCells)
                ->color('success')
                ->modalHeading('Histórico de Precios — Excel')
                ->modalDescription('Filtra opcionalmente y descarga el reporte en formato .xlsx.')
                ->schema($sharedFilters)
                ->action(function (array $data) {
                    return redirect()->route('admin.servicios.reportes.historico-precios.excel', [
                        'categoria_id' => $data['categoria_id'] ?? null,
                        'servicio_id' => $data['servicio_id'] ?? null,
                        'moneda_id' => $data['moneda_id'] ?? null,
                        'estado' => $data['estado'] ?: null,
                    ]);
                }),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('Servicios:ReporteHistoricoPrecios') ?? false;
    }
}
