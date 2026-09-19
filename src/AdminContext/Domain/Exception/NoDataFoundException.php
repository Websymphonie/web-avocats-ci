<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Enum\StringEnum;

class NoDataFoundException extends DomainException implements UserFacingError
{
    public function __construct(string $message = StringEnum::NO_DATA_FOUND_TEXT->value)
    {
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.data_not_found';
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
