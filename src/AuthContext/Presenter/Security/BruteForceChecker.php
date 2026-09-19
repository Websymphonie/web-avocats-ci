<?php declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Security;

use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuthLog\AuthLogRepository;

readonly class BruteForceChecker
{
    public function __construct(
        private AuthLogRepository $authLogRepository
    )
    {
    }

    /**
     * @param string $emailEntered
     * @param string|null $userIP
     */
    public function addFailedAttempt(
        string  $emailEntered,
        ?string $userIP
    ): void
    {
        // Symfony Security gère désormais le blocage via login_throttling.
        // Cette classe conserve uniquement la trace d’audit des échecs.
        $this->authLogRepository->addFailedAuthAttempt($emailEntered, $userIP);
    }
}
