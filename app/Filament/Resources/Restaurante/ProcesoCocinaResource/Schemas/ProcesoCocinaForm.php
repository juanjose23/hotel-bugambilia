<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Schemas;

use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Queries\Restaurante\Cocina\GuiaRecetaCocinaQuery;
use App\Repository\Queries\Restaurante\Cocina\ObtenerDatosProcesoCocinaQuery;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

final class ProcesoCocinaForm
{
    public static function configure(Schema $schema): Schema
    {
        $record = $schema->getRecord();
        $editable = ! $record instanceof ProcesoCocina || ($record->getAttribute('tipo_registro') === 'borrador' && $record->getAttribute('confirmado_at') === null && $record->getAttribute('pedido_item_id') === null);

        return $schema->components([
            Section::make('Tu asistente de recetas')->disabled(! $editable)->description('Primero planifica. Después prepara siguiendo la ficha y confirma lo que realmente obtuviste. El inventario se descuenta una sola vez, al confirmar.')->columnSpanFull()->schema([
                Wizard::make([
                    Step::make('Elegir receta')->description('Platillo y porciones')->icon('heroicon-o-book-open')->schema([
                        Select::make('plato_id')->label('¿Qué platillo vas a preparar?')->options(fn (): array => app(ObtenerDatosProcesoCocinaQuery::class)->platosConReceta())->searchable()->required()->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                $plato = is_numeric($state) ? Plato::query()->find((int) $state) : null;
                                $set('producto_origen_id', $plato?->producto_receta_id);
                            }),
                        TextInput::make('cantidad_platos')->label('¿Cuántas porciones necesitas?')->integer()->numeric()->minValue(1)->required()->default(1)->live(onBlur: true)->helperText('El sistema calcula los ingredientes a partir del rendimiento de la receta.'),
                        Hidden::make('producto_origen_id')->required(), Hidden::make('cantidad_procesada')->default(1),
                    ]),
                    Step::make('Revisar ingredientes')->description('Cantidades y existencias')->icon('heroicon-o-scale')->schema([
                        self::guia('ingredientes', 'filament.pages.restaurante.components.guia-ingredientes-receta'),
                    ]),
                    Step::make('Aprender y planificar')->description('Ficha y siguiente paso')->icon('heroicon-o-academic-cap')->schema([
                        self::guia('instrucciones', 'filament.pages.restaurante.components.guia-instrucciones-receta'),
                        Textarea::make('observaciones')->label('Nota para el equipo (opcional)')->rows(2)->maxLength(2000),
                    ]),
                ])->nextAction(fn (Action $action): Action => $action->label('Continuar'))->previousAction(fn (Action $action): Action => $action->label('Volver'))
                    ->submitAction($editable ? new HtmlString(Blade::render('<x-filament::button type="submit" wire:loading.attr="disabled">Guardar planificación</x-filament::button>')) : null),
            ]),
        ]);
    }

    /** @param view-string $vista */
    private static function guia(string $nombre, string $vista): ViewField
    {
        return ViewField::make('guia_'.$nombre)->view($vista)->dehydrated(false)->viewData(function (Get $get): array {
            $id = $get('plato_id');
            $porciones = $get('cantidad_platos');

            return ['guia' => app(GuiaRecetaCocinaQuery::class)->ejecutar(is_numeric($id) ? (int) $id : null, is_numeric($porciones) ? (int) $porciones : 1)];
        });
    }
}
