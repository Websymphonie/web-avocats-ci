<?php

declare(strict_types=1);

namespace Websymphonie\Tests\AuthContext\Application\Service\Email;

use DateTime;
use PHPUnit\Framework\TestCase;
use Websymphonie\AuthContext\Application\Service\Email\ResetSendEmail;
use Websymphonie\AuthContext\Domain\Enum\ResetTypeEnum;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final class ResetSendEmailTest extends TestCase
{
    public function testTokenEmailUsesTheUsersNameAndResetLinkTemplate(): void
    {
        $email = $this->createEmail(ResetTypeEnum::TOKEN, 'secure-token');

        self::assertSame('reset_password/email', $email->template());
        self::assertSame('Awa Koné', $email->templateVariables()['fullname']);
        self::assertSame('secure-token', $email->templateVariables()['resetToken']);
    }

    public function testOtpEmailExposesTheCodeAndExpiration(): void
    {
        $email = $this->createEmail(ResetTypeEnum::OTP, '483921');

        self::assertSame('reset_password/email_otp_code', $email->template());
        self::assertSame('483921', $email->templateVariables()['code']);
        self::assertInstanceOf(DateTime::class, $email->templateVariables()['date']);
    }

    private function createEmail(ResetTypeEnum $type, string $token): ResetSendEmail
    {
        $user = (new User())
            ->setName('Awa Koné')
            ->setEmail('awa@example.test');
        $expiresAt = new DateTime('+5 minutes');
        $resetPassword = (new ResetPassword())
            ->setResetType($type->value)
            ->setPasswordResetExpiresAt($expiresAt);
        $user->setResetPassword($resetPassword);

        return new ResetSendEmail(
            recipient: new Email('awa@example.test'),
            title: 'Réinitialisation de mot de passe',
            user: $user,
            resetPassword: $resetPassword,
            date: $expiresAt,
            token: $token,
        );
    }
}
