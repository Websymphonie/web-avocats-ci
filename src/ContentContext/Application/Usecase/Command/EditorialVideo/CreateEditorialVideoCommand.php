<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo;

use Websymphonie\ContentContext\Domain\Enum\VideoProvider;

final class CreateEditorialVideoCommand
{
    /** @param list<int> $tags */
    public function __construct(public string $title = '', public ?string $excerpt = null, public string $description = '', public VideoProvider $provider = VideoProvider::YOUTUBE, public string $videoUrl = '', public array $tags = []) {}
}
