<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final class CreateNewsCommand
{
    public function __construct(public string $title = '', public ?string $excerpt = null, public string $body = '') {}
}
