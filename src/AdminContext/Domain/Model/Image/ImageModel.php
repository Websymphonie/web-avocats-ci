<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Model\Image;

use DateTimeImmutable;

final class ImageModel
{
    public function __construct(
        public ?int               $id = null,
        public ?string            $name = null,
        public ?string            $label = null,
        public ?string            $url = null,
        public ?DateTimeImmutable $updatedAt = null,
    )
    {
    }
}
