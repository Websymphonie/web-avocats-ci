<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Batonnier;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateBatonnierMandateCommand
{
    public function __construct(
        public int $id,
        public string $fullName = '',
        public ?UploadedFile $portrait = null,
        public ?DateTimeImmutable $mandateStartedAt = null,
        public ?DateTimeImmutable $mandateEndedAt = null,
        public ?string $summary = null,
        public bool $removePortrait = false,
    ) {
    }
}
