<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Exception;

use DomainException;
final class InvalidArgument extends DomainException implements UserFacingError
{
    /**
     * @phpstan-pure
     *
     * @param array<string, mixed> $translationParameters
     */
    public function __construct(
        string                  $message,
        private readonly string $translationDomain = 'shared_context',
        private readonly array  $translationParameters = []
    )
    {
        parent::__construct($message);
    }

    public function translationId(): string
    {
        return $this->getMessage();
    }

    public function translationDomain(): string
    {
        return $this->translationDomain;
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return $this->translationParameters;
    }
}
