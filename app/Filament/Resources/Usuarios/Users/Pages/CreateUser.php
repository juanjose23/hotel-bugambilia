<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users\Pages;

use App\Filament\Resources\Usuarios\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
