<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Repository\Models\User;
use Filament\Pages\Dashboard as FilamentDashboard;

final class Dashboard extends FilamentDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->is_admin === true;
    }

    public function getHeading(): ?string
    {
        return null;
    }
}
