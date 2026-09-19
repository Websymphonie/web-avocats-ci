<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Service\Email;

use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Application\Service\Mailing\EmailDefinition;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final class ResetSendEmail implements EmailDefinition
{
    private ?string $legacyToken = null;
    private ?string $legacyType = null;

    public function __construct(
        private Email $recipient,
        private string $title,
        private User $user,
        private ?ResetPassword $resetPassword = null,
        private ?string $resetUrl = null,
        private ?\DateTimeImmutable $expiresAt = null,
        ?\DateTimeInterface $date = null,
        ?string $token = null,
    )
    {
        $this->legacyToken = $token;
        $this->legacyType = $resetPassword?->getResetType();
        $this->expiresAt ??= $date === null ? null : \DateTimeImmutable::createFromInterface($date);
    }

    /**
     * @return Email
     */
    public function recipient(): Email
    {
        return $this->recipient;
    }

    /**
     * @return array<string, mixed>
     */
    public function subjectVariables(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return $this->legacyType === 'otp_code' ? 'reset_password/email_otp_code' : 'reset_password/email';
    }

    /**
     * @return array<string, mixed>
     */
    public function templateVariables(): array
    {
        return [
            'title' => $this->title,
            'fullname' => $this->user->getName() ?? $this->user->getEmail(),
            'user' => $this->user,
            'resetUrl' => $this->resetUrl,
            'expiresAt' => $this->expiresAt,
            'resetToken' => $this->legacyToken,
            'code' => $this->legacyToken,
            'date' => $this->expiresAt === null ? null : ($this->legacyType === 'otp_code' ? \DateTime::createFromImmutable($this->expiresAt) : $this->expiresAt),
        ];
    }

    /**
     * @return string
     */
    public function subject(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return 'fr';
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return 'auth_context';
    }
}
