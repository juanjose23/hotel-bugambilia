<?php

declare(strict_types=1);

namespace App\Filament\Pages\Servicios;

use App\Enums\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Catalogo;
use App\Models\Monedas\Moneda;
use App\Models\Servicios\Servicio;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReporteHistoricoPrecios extends Page
{
    protected string $view = 'filament.pages.servicios.reporte-historico-precios';

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
                ->options(fn () => Catalogo::whereHas(
                    'catalogoTipo',
                    fn ($q) => $q->where('codigo', CatalogoTipo::CATEGORIA_SERVICIO->value)
                )->pluck('nombre', 'id')->toArray())
                ->searchable()
                ->placeholder('Todas las categorías'),
            Select::make('servicio_id')
                ->label('Filtrar por Servicio (Opcional)')
                ->options(fn () => Servicio::pluck('nombre', 'id')->toArray())
                ->searchable()
                ->placeholder('Todos los servicios'),
            Select::make('moneda_id')
                ->label('Filtrar por Moneda (Opcional)')
                ->options(fn () => Moneda::pluck('nombre', 'id')->toArray())
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
                    return redirect()->route('reporte.servicios.historico-precios.pdf', [
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
                    return redirect()->route('reporte.servicios.historico-precios.excel', [
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
