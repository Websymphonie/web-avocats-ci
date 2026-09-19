<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception;

use Websymphonie\SharedContext\Domain\Exception\UserFacingError as SharedUserFacingError;

/** @deprecated Use the shared contract for new exceptions. */
interface UserFacingError extends SharedUserFacingError
{
}
