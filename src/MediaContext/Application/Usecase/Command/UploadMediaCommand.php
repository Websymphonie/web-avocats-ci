<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Usecase\Command;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UploadMediaCommand
{
    public function __construct(public UploadedFile $file) {}
}
