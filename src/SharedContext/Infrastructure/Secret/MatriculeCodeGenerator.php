<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Secret;

use Random\RandomException;
use Websymphonie\SharedContext\Domain\Model\ValueObject\GeneratedMatriculeCode;

readonly class MatriculeCodeGenerator implements MatriculeGeneratorInterface
{
    private const string ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * @throws RandomException
     */
    public function generate(string $firstName, string $lastName): GeneratedMatriculeCode
    {
        return new GeneratedMatriculeCode(codeMatricule: $this->code($firstName, $lastName));
    }

    /**
     * @throws RandomException
     */
    private function code(string $firstName, string $lastName): string
    {
        $initials = strtoupper(
            mb_substr($lastName, 0, 1) .
            mb_substr($firstName, 0, 1)
        );

        // 3 ou 4 caractères pour avoir 5–6 au total
        $randomLength = random_int(3, 4);
        $randomPart = $this->randomAlphaNumeric($randomLength);

        return sprintf("%s-%s", $initials, $randomPart);
    }

    /**
     * @throws RandomException
     */
    private function randomAlphaNumeric(int $length): string
    {
        $code = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }
}
