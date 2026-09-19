<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\Activation;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class AccountActivationUnavailableException extends RuntimeException implements UserFacingError
{
    public function __construct() { parent::__construct('Account activation unavailable.'); }
    public function translationId(): string { return 'exceptions.account_activation.unavailable'; }
    public function translationDomain(): string { return 'identity_context'; }
    public function translationParameters(): array { return []; }
}
