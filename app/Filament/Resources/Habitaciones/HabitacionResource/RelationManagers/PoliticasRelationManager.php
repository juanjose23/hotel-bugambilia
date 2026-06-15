<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasPoliticasAttachDetach;
use Filament\Resources\RelationManagers\RelationManager;

class PoliticasRelationManager extends RelationManager
{
    use HasPoliticasAttachDetach;

    protected static string $relationship = 'politicas';

    protected static ?string $title = 'Políticas';

    protected static ?string $label = 'Política';

    protected static ?string $pluralLabel = 'Políticas';

    protected static ?string $inverseRelationship = 'habitaciones';
}
