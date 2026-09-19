<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Intl;

readonly class WsIntlFormatter
{
    public static function getFormatted(string $number): ?string
    {
        if (!$number) {
            return null;
        }

        // Nettoyage
        $digits = preg_replace('/\D+/', '', $number);

        // Supprimer indicatif si présent
        if (str_starts_with($digits, '225')) {
            $digits = substr($digits, 3);
        }

        if (strlen($digits) !== 10) {
            return $number; // fallback
        }

        $chunks = str_split($digits, 2);

        return '+225 ' . implode(' ', $chunks);
    }

    public static function getTelLink(string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        if (!str_starts_with($digits, '225')) {
            $digits = '225' . $digits;
        }

        return '+' . $digits;
    }
}