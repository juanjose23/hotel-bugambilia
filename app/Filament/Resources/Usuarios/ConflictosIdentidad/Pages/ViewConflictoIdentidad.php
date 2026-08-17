<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad\Pages;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\ConflictoIdentidadResource;
use App\Interactors\Usuarios\Identidad\ResolverConflictoIdentidad;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use App\Support\CachedOptions;
use App\Support\UsuarioAutenticado;
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
                        ->options(fn () => CachedOptions::catalogosPorVarios(['TIPO_CLIENTE', 'tipo_cliente']))
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
                        $interactor->vincular($record, $data, UsuarioAutenticado::id());

                        Notification::make()
                            ->title('Conflicto resuelto')
                            ->body('Cliente vinculado exitosamente.')
                            ->success()
                            ->send();

                        $this->redirect(ConflictoIdentidadResource::getUrl());
                    } catch (YaTieneCuentaException $e) {
                        Notification::make()
                            ->title('Este correo ya tiene una cuenta')
                            ->body('El correo electrónico ingresado ya está vinculado a una cuenta existente. Verifique los datos.')
                            ->warning()
                            ->send();

                        $this->halt();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('No se pudo resolver el conflicto')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        $this->halt();
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

                    try {
                        $interactor->rechazar($record, is_string($data['notas']) ? $data['notas'] : '', UsuarioAutenticado::id());

                        Notification::make()
                            ->title('Conflicto rechazado')
                            ->success()
                            ->send();

                        $this->redirect(ConflictoIdentidadResource::getUrl());
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('No se pudo rechazar el conflicto')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }
}
