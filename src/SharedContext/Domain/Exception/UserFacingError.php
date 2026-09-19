<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Exception;

use Throwable;

interface UserFacingError extends Throwable
{
    public function translationId(): string;

    public function translationDomain(): string;

    /** @return array<string, scalar|null> */
    public function translationParameters(): array;
}
