<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Helper;

use NumberToWords\Exception\InvalidArgumentException;
use NumberToWords\Exception\NumberToWordsException;
use NumberToWords\NumberToWords;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Websymphonie\SharedContext\Domain\Service\Helper\HelpersInterfaces;

readonly class HelpersServices implements HelpersInterfaces
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public static function cleanPhoneNumber(string $phoneNumber): string
    {
        // 1. Supprimer tout sauf les chiffres
        $digits = preg_replace('/\D+/', '', $phoneNumber);

        // 2. Garder uniquement les 10 derniers chiffres
        return substr($digits, -10);
    }

    public static function toUcWords(?string $text = null): ?string
    {
        if ($text === null) return null;

        $text = mb_strtolower($text, 'UTF-8');

        // Majuscule après espace, tiret ou apostrophe
        return preg_replace_callback('/(^|[\s\'-])(\p{L})/u', function ($matches) {
            return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
        }, $text);
    }

    /**
     * @param float $numberToConvert
     * @param string|null $devise
     * @return string
     * @throws InvalidArgumentException
     * @throws NumberToWordsException
     */
    public function tocurrency(float $numberToConvert, ?string $devise = 'XOF'): string
    {
        if (!$devise) {
            return '';
        }
        $montant = 0;
        if ($numberToConvert) {
            $montant = $montant + ($numberToConvert * 100);
        } else {
            $montant = $montant + $numberToConvert;
        }
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale();
        $numberToWords = new NumberToWords();
        $currencyTransformer = $numberToWords->getCurrencyTransformer($locale);
        return $currencyTransformer->toWords(intval($montant), $devise);
    }

    /**
     * @param int $numberToConvert
     * @return string
     * @throws InvalidArgumentException
     * @throws NumberToWordsException
     */
    public function towords(int $numberToConvert): string
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale();
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer($locale);
        return $numberTransformer->toWords($numberToConvert);
    }

    /** @return array<int, int> */
    public function getYears(): array
    {
        $years = [];
        for ($y = 2026; $y <= $this->currentYear(); $y++) {
            $years[$y] = $y;
        }

        return $years;
    }

    public function currentYear(): int
    {
        return (int)date('Y');
    }
}
