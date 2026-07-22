<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs\Pages;

use App\Filament\Resources\Auditoria\AuditoriaJobs\AuditoriaJobResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditoriaJob extends ViewRecord
{
    protected static string $resource = AuditoriaJobResource::class;
}
