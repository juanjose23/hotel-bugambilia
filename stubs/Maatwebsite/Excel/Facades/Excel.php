<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Facades;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @method static BinaryFileResponse download(object $export, string $fileName, ?string $writerType = null, array $excelAttributes = [])
 * @method static bool store(object $export, string $filePath, ?string $disk = null, ?string $writerType = null, array $excelAttributes = [])
 */
class Excel {}
