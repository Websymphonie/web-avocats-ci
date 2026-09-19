<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\Activation;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedToken;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\SecretGeneratorInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation\AccountActivation;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final readonly class AccountActivationIssuer
{
    public function __construct(
        private SecretGeneratorInterface $secretGenerator,
        private AccountActivationRepositoryInterface $repository,
        private ClockInterface $clock,
        private string $activationTtl = '24 hours',
    ) {
    }

    /** @return array{activation: AccountActivation, token: string} */
    public function issue(User $user): array
    {
        $now = $this->clock->now();
        $expiresAt = $now->modify($this->activationTtl);
        if ($expiresAt <= $now) {
            throw new \InvalidArgumentException('Invalid account activation TTL.');
        }
        $this->repository->invalidateForUser($user, $now);
        $selector = bin2hex(random_bytes(16));
        /** @var GeneratedToken $generated */
        $generated = $this->secretGenerator->generateToken(64);
        $token = $generated->token;
        $activation = new AccountActivation($user, $selector, hash('sha256', $token), $expiresAt, $now);
        $this->repository->create($activation);
        $user->setAccountMustBeVerifedBefore($expiresAt);
        return ['activation' => $activation, 'token' => $token];
    }
}
