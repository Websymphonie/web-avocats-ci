<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Model;

final class PagePersonGroupInput
{
    public string $key = '';
    public string $title = '';
    public int $sortOrder = 0;

    /** @var list<PagePersonEntryInput> */
    public array $entries = [];
}
