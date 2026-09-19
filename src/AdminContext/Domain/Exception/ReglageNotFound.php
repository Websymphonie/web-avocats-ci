<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class ReglageNotFound extends DomainException implements UserFacingError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Aucun reglage ne correspond à cet identifiant %d', $id));
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.reglages.reglage_not_found';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'admin_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}
