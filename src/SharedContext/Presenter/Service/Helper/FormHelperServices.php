<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Helper;

use DateTime;
use IntlDateFormatter;

readonly class FormHelperServices
{
    /**
     * Retourne un tableau associatif des mois localisés :
     * ex : ['Janvier' => 1, 'Février' => 2, ..., 'Décembre' => 12]
     */
    /** @return array<string, int> */
    public static function getMonthChoices(string $locale = 'fr_FR'): array
    {
        $months = [];
        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            'LLLL' // format du nom complet du mois
        );

        foreach (range(1, 12) as $m) {
            $date = DateTime::createFromFormat('!m', (string)$m);
            $monthName = ucfirst($formatter->format($date)); // Ex : "Janvier"
            $months[$monthName] = $m;
        }

        return $months;
    }
}
