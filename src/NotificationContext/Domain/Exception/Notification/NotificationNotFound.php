<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Exception\Notification;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class NotificationNotFound extends DomainException implements UserFacingError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Aucune notification ne correspond à cet identifiant %d', $id));
    }

    public function getMessageKey(): string
    {
        return "Aucune notification trouvée.";
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.notifications.notification_not_found';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'notification_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}
