<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Log;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;

class LogModel
{
    /**
     * @param array<string, mixed>|null $context
     * @param array<string, mixed>|null $extra
     */
    public function __construct(
        public ?int               $id = null,
        public ?string            $uuid = null,
        public ?string            $message = null,
        public ?array             $context = null,
        public ?int               $level = null,
        public ?string            $levelName = null,
        public ?array             $extra = null,
        public ?UserModel         $user = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    )
    {
    }
}
