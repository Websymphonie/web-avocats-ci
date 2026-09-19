<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Enum;

use InvalidArgumentException;

enum ResetTypeEnum: string
{
    case OTP = 'otp_code';
    case TOKEN = 'token_code';

    public const string LABEL_OTP = 'Code OTP';
    public const string LABEL_TOKEN = 'Token';

    public static function getValue(string $value): ResetTypeEnum
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException(sprintf('Unknown reset type "%s".', $value));
    }

    public function label(): string
    {
        return match ($this) {
            self::OTP => self::LABEL_OTP,
            self::TOKEN => self::LABEL_TOKEN,
        };
    }
}
