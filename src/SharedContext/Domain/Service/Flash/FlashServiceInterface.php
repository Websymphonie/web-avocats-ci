<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Flash;

use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

interface FlashServiceInterface
{
    public function success(string $message): void;

    public function info(string $message): void;

    public function warning(string $message): void;

    public function danger(string $message): void;

    public function errorFromException(UserFacingError $exception): void;

}
