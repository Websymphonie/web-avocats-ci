<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final class UpdatePhotoGalleryCommand
{
    /**
     * @param list<int> $tags
     * @param array<int, array{altText?: string, caption?: string|null}> $items
     */
    public function __construct(public int $id, public string $title = '', public string $description = '', public array $tags = [], public array $items = []) {}
}
