<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service\RichText;

interface RichTextSanitizerInterface
{
    public function sanitize(string $html): string;
}
