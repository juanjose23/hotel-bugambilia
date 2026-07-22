<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad\Pages;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\ConflictoIdentidadResource;
use App\Interactors\Usuarios\ResolverConflictoIdentidad;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewConflictoIdentidad extends ViewRecord
{
    protected static string $resource = ConflictoIdentidadResource::class;

    protected function getHeaderActions(): array
    {
        /** @var ConflictoIdentidad $record */
        $record = $this->record;

        return [
            Action::make('vincular')
                ->label('Vincular y Crear Cliente')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $record->estado === EstadoConflictoIdentidad::Pendiente)
                ->schema([
                    Select::make('catalogo_id')
                        ->label('Tipo de Cliente')
                        ->options(
                            fn (): array => Catalogo::whereHas('catalogoTipo', function ($query) {
                                $query->whereIn('codigo', ['TIPO_CLIENTE', 'tipo_cliente']);
                            })->pluck('nombre', 'id')->toArray()
                        )
                        ->required()
                        ->native(false)
                        ->searchable(),
                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->unique(table: 'users', column: 'email'),
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->revealable(),
                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $interactor = app(ResolverConflictoIdentidad::class);

                    try {
                        $resultado = $interactor->vincular($record, $data);

                        Notification::make()
                            ->title('Conflicto resuelto')
                            ->body('Cliente vinculado exitosamente.')
                            ->success()
                            ->send();

                        $this->redirect(ConflictoIdentidadResource::getUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al vincular')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('rechazar')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $record->estado === EstadoConflictoIdentidad::Pendiente)
                ->requiresConfirmation()
                ->modalHeading('Rechazar Conflicto')
                ->modalDescription('¿Está seguro de rechazar este conflicto de identidad?')
                ->schema([
                    Textarea::make('notas')
                        ->label('Motivo del Rechazo')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $interactor = app(ResolverConflictoIdentidad::class);
                    $interactor->rechazar($record, is_string($data['notas']) ? $data['notas'] : '');

                    Notification::make()
                        ->title('Conflicto rechazado')
                        ->success()
                        ->send();

                    $this->redirect(ConflictoIdentidadResource::getUrl());
                }),
        ];
    }
}
