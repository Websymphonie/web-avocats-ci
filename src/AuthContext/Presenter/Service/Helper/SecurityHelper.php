<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Service\Helper;

use Random\RandomException;
use Websymphonie\IdentityContext\Domain\Service\Helper\SecurityHelperInterface;

class SecurityHelper implements SecurityHelperInterface
{
    public function passwordVerify(string $password, string $passwordConfirm): bool
    {
        if ($password !== $passwordConfirm) {
            return false;
        }
        return true;
    }

    /**
     * @throws RandomException
     */
    public function generateOTP(int $length): int
    {
        $characters = '0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return intval($code);
    }

    /**
     * @throws RandomException
     */
    public function generateToken(int $tokenLength = 64): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';

        for ($i = 0; $i < $tokenLength; $i++) {
            $token .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $token;
    }
}