<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\Password;


use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedCode;
use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedToken;

interface SecretGeneratorInterface
{
    public function generateToken(int $length = 60): GeneratedToken;

    public function generateCode(int $length = 6): GeneratedCode;
}