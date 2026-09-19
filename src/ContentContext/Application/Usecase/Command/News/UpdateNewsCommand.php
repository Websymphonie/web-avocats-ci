<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final class UpdateNewsCommand
{
    public function __construct(public int $id, public string $title = '', public ?string $excerpt = null, public string $body = '') {}
}
