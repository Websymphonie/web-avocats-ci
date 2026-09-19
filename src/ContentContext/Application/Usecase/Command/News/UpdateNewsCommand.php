<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final class UpdateNewsCommand
{
    /**
     * @param list<int> $categories
     * @param list<int> $tags
     */
    public function __construct(public int $id, public string $title = '', public ?string $excerpt = null, public string $body = '', public array $categories = [], public array $tags = []) {}
}
