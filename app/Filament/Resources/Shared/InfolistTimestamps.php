<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class InfolistTimestamps
{
    /**
     * @return array<int, TextEntry>
     */
    public static function make(
        ?string $format = 'd/m/Y H:i',
        bool $since = false,
        bool $withIcons = false,
        ?TextSize $size = null
    ): array {
        $created = TextEntry::make('created_at')
            ->label('Creado')
            ->dateTime($format)
            ->placeholder('-');

        $updated = TextEntry::make('updated_at')
            ->label('Actualizado')
            ->dateTime($format)
            ->placeholder('-');

        if ($since) {
            $created->since();
            $updated->since();
        }

        if ($withIcons) {
            $created->icon(Heroicon::PlusCircle);
            $updated->icon(Heroicon::PencilSquare);
        }

        if ($size !== null) {
            $created->size($size);
            $updated->size($size);
        }

        return [$created, $updated];
    }
}
