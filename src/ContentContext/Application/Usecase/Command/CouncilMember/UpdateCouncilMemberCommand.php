<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateCouncilMemberCommand
{
    public function __construct(
        public int $id,
        public string $fullName = '',
        public string $function = '',
        public ?UploadedFile $portrait = null,
        public int $sortOrder = 0,
        public ?DateTimeImmutable $mandateStartedAt = null,
        public ?DateTimeImmutable $mandateEndedAt = null,
        public bool $removePortrait = false,
    ) {
    }
}
