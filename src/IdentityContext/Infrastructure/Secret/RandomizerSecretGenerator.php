<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Secret;

use Random\RandomException;
use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedCode;
use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedToken;
use Websymphonie\IdentityContext\Domain\Service\Password\SecretGeneratorInterface;

class RandomizerSecretGenerator implements SecretGeneratorInterface
{
    private const string ALLOWED_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * @param int $length
     * @return GeneratedToken
     * @throws RandomException
     */
    public function generateToken(int $length = 60): GeneratedToken
    {
        $characters = self::ALLOWED_CHARACTERS;
        $charLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charLength - 1);
            $randomString .= $characters[$index];
        }

        return new GeneratedToken($randomString);
    }

    /**
     * @param int $length
     * @return GeneratedCode
     * @throws RandomException
     */
    public function generateCode(int $length = 6): GeneratedCode
    {
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;
        $code = (string)random_int($min, $max);
        return new GeneratedCode($code);
    }
}