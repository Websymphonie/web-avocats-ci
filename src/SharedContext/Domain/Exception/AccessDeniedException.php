<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Exception;

use DomainException;
final class AccessDeniedException extends DomainException implements UserFacingError
{
    public function __construct(
        private readonly string $groupName,
    )
    {
        parent::__construct('Accès refusé au groupe');
    }

    public function translationId(): string
    {
        return 'exceptions.accessDenied.access_denied';
    }

    public function translationDomain(): string
    {
        return 'shared_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [
            '%groupName%' => $this->groupName,
        ];
    }
}
