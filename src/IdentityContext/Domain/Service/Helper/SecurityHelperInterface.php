<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\Helper;

interface SecurityHelperInterface
{
    public function passwordVerify(string $password, string $passwordConfirm): bool;

    public function generateOTP(int $length): int;

    public function generateToken(int $tokenLength = 64): string;
}