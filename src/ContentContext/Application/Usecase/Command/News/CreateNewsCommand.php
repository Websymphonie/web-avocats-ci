<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final class CreateNewsCommand
{
    /**
     * @param list<int> $categories
     * @param list<int> $tags
     */
    public function __construct(public string $title = '', public ?string $excerpt = null, public string $body = '', public array $categories = [], public array $tags = []) {}
}
