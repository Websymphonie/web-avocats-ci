<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Repository;

use Websymphonie\MediaContext\Domain\Model\Media;

interface MediaRepositoryInterface
{
    public function save(Media $media): Media;

    public function getById(int $id): Media;

    public function delete(Media $media): void;
}
