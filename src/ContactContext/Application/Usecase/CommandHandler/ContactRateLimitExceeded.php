<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\CommandHandler;

use RuntimeException;

final class ContactRateLimitExceeded extends RuntimeException
{
}
