<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\Email;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Application\Service\Mailing\EmailDefinition;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class AccountActivationEmail implements EmailDefinition
{
    public function __construct(private Email $recipient, private User $user, private string $activationUrl, private DateTimeImmutable $expiresAt) {}
    public function recipient(): Email { return $this->recipient; }
    public function subjectVariables(): array { return []; }
    public function template(): string { return 'user/account_activation'; }
    public function templateVariables(): array { return ['fullname' => $this->user->getName() ?? $this->user->getEmail(), 'activationUrl' => $this->activationUrl, 'expiresAt' => $this->expiresAt]; }
    public function subject(): string { return 'Activez votre compte KLE Immobilier'; }
    public function locale(): string { return 'fr'; }
    public function getDomain(): string { return 'identity_context'; }
}
